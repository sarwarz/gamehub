<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Models\RefundRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * @group Refund Requests
 *
 * APIs for customers to request refunds and for sellers
 * to view refund requests on their items.
 */
class RefundRequestController extends Controller
{
    /**
     * List my refund requests
     *
     * Get all refund requests submitted by the authenticated user.
     *
     * @authenticated
     *
     * @queryParam status string Filter by status: pending, approved, rejected, processing, completed. Example: pending
     * @queryParam per_page integer Results per page. Example: 10
     *
     * @response 200 {"status":true,"message":"Refund requests fetched","data":{"current_page":1,"data":[],"total":0}}
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $refunds = RefundRequest::where('user_id', $request->user()->id)
                ->with([
                    'order:id,order_number,total_amount,status',
                    'orderItem:id,product_id',
                    'orderItem.product:id,title,slug,image',
                ])
                ->when($request->status, fn ($q, $s) => $q->where('status', $s))
                ->latest()
                ->paginate(min($request->integer('per_page', 15), 50));

            return $this->success($refunds, 'Refund requests fetched');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Unable to fetch refund requests.', 500);
        }
    }

    /**
     * Submit refund request
     *
     * Request a refund for an order or specific order item.
     *
     * @authenticated
     *
     * @bodyParam order_id integer required Order ID. Example: 1
     * @bodyParam order_item_id integer optional Specific item to refund (for partial refund). Example: 3
     * @bodyParam type string required Refund type: full or partial. Example: full
     * @bodyParam amount numeric required for partial, ignored for full. Refund amount. Example: 15.99
     * @bodyParam reason string required Reason for refund. Example: Product key not working
     * @bodyParam description string optional Detailed description. Example: I tried activating the key multiple times but it says already redeemed.
     *
     * @response 201 {"status":true,"message":"Refund request submitted","data":{}}
     * @response 404 {"status":false,"message":"Order not found"}
     * @response 409 {"status":false,"message":"A refund request already exists for this order"}
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'order_id'      => 'required|integer',
            'order_item_id' => 'nullable|integer',
            'type'          => 'required|in:full,partial',
            'amount'        => 'required_if:type,partial|numeric|min:0.01|max:999999.99',
            'reason'        => 'required|string|max:255',
            'description'   => 'nullable|string|max:2000',
        ]);

        try {
            $refundSettings = Setting::group('refund_escrow');

            if (isset($refundSettings['refund_enabled']) && !$refundSettings['refund_enabled']) {
                return $this->error('Refund requests are currently disabled.', 403);
            }

            $allowPartial = $refundSettings['partial_refund_enabled'] ?? true;

            if ($request->type === 'partial' && !$allowPartial) {
                return $this->error('Partial refunds are not allowed.', 422);
            }

            $order = Order::where('id', $request->order_id)
                ->where('user_id', $request->user()->id)
                ->first();

            if (!$order) {
                return $this->error('Order not found', 404);
            }

            if (!in_array($order->status, ['completed', 'processing'])) {
                return $this->error('Refund can only be requested for completed or processing orders', 422);
            }

            $autoRefundHours = (int) ($refundSettings['auto_refund_window_hours'] ?? 0);
            if ($autoRefundHours > 0 && $order->paid_at) {
                $refundDeadline = $order->paid_at->addHours($autoRefundHours);
                if (now()->greaterThan($refundDeadline)) {
                    return $this->error(
                        "Refund window has expired. Refunds must be requested within {$autoRefundHours} hours of payment.",
                        422
                    );
                }
            }

            $sellerId = null;
            $maxAmount = $order->total_amount;

            if ($request->order_item_id) {
                $item = OrderItem::where('id', $request->order_item_id)
                    ->where('order_id', $order->id)
                    ->first();

                if (!$item) {
                    return $this->error('Order item not found', 404);
                }

                $sellerId = $item->seller_id;
                $maxAmount = $item->subtotal;
                $amount = $request->type === 'partial' ? $request->amount : $item->subtotal;
            } else {
                $amount = $request->type === 'partial' ? $request->amount : $order->total_amount;
            }

            if ($amount > $maxAmount) {
                return $this->error("Refund amount cannot exceed " . number_format($maxAmount, 2) . ".", 422);
            }

            $previouslyRefunded = RefundRequest::where('order_id', $order->id)
                ->whereIn('status', ['pending', 'approved', 'processing', 'completed'])
                ->when($request->order_item_id, fn ($q) => $q->where('order_item_id', $request->order_item_id))
                ->sum('amount');

            if (($previouslyRefunded + $amount) > $maxAmount) {
                $remaining = max(0, $maxAmount - $previouslyRefunded);
                return $this->error("Cumulative refund would exceed the order total. Remaining refundable: " . number_format($remaining, 2) . ".", 422);
            }

            $maxRefundPct = (float) ($refundSettings['max_refund_percentage'] ?? 100);
            $maxAllowed = round($maxAmount * ($maxRefundPct / 100), 2);
            if ($amount > $maxAllowed) {
                return $this->error("Maximum refund amount is {$maxAllowed} ({$maxRefundPct}% of order total).", 422);
            }

            $refund = DB::transaction(function () use ($request, $order, $sellerId, $amount) {
                $exists = RefundRequest::where('order_id', $order->id)
                    ->where('user_id', $request->user()->id)
                    ->whereIn('status', ['pending', 'processing', 'approved'])
                    ->when($request->order_item_id, fn ($q) => $q->where('order_item_id', $request->order_item_id))
                    ->when(!$request->order_item_id, fn ($q) => $q->whereNull('order_item_id'))
                    ->lockForUpdate()
                    ->exists();

                if ($exists) {
                    return null;
                }

                return RefundRequest::create([
                    'order_id'      => $order->id,
                    'order_item_id' => $request->order_item_id,
                    'user_id'       => $request->user()->id,
                    'seller_id'     => $sellerId,
                    'type'          => $request->type,
                    'amount'        => $amount,
                    'reason'        => $request->reason,
                    'description'   => $request->description,
                    'status'        => 'pending',
                ]);
            });

            if (!$refund) {
                return $this->error('A refund request already exists for this order', 409);
            }

            try {
                $refund->load('order');
                if (Setting::get('refund_notifications', 'customer_on_requested', true)) {
                    $request->user()->notify(new \App\Notifications\RefundRequestedNotification($refund, 'customer'));
                }
                if (Setting::get('refund_notifications', 'admin_on_requested', true)) {
                    $admins = \App\Models\User::whereHas('roles', fn($q) => $q->whereIn('name', ['admin','superadmin']))->get();
                    $admins->each(fn($a) => $a->notify(new \App\Notifications\RefundRequestedNotification($refund, 'admin')));
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Refund requested notification failed: ' . $e->getMessage());
            }

            return $this->success(
                $refund->load(['order:id,order_number', 'orderItem.product:id,title']),
                'Refund request submitted',
                201
            );
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Unable to submit refund request.', 500);
        }
    }

    /**
     * Show refund request
     *
     * Get details of a specific refund request.
     *
     * @authenticated
     *
     * @urlParam id integer required Refund request ID. Example: 1
     *
     * @response 200 {"status":true,"message":"Refund request details","data":{}}
     * @response 404 {"status":false,"message":"Refund request not found"}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $refund = RefundRequest::where('user_id', $request->user()->id)
                ->with([
                    'order:id,order_number,total_amount,status,payment_method',
                    'orderItem:id,product_id,quantity,subtotal',
                    'orderItem.product:id,title,slug,image',
                ])
                ->find($id);

            if (!$refund) {
                return $this->error('Refund request not found', 404);
            }

            return $this->success($refund, 'Refund request details');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Unable to fetch refund request details.', 500);
        }
    }

    /**
     * Cancel refund request
     *
     * Cancel a pending refund request.
     *
     * @authenticated
     *
     * @urlParam id integer required Refund request ID. Example: 1
     *
     * @response 200 {"status":true,"message":"Refund request cancelled"}
     * @response 422 {"status":false,"message":"Only pending requests can be cancelled"}
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        try {
            $refund = RefundRequest::where('user_id', $request->user()->id)->find($id);

            if (!$refund) {
                return $this->error('Refund request not found', 404);
            }

            if ($refund->status !== 'pending') {
                return $this->error('Only pending requests can be cancelled', 422);
            }

            $refund->update(['status' => 'cancelled']);

            return $this->success(null, 'Refund request cancelled');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Unable to cancel refund request.', 500);
        }
    }

    /**
     * Seller refund requests
     *
     * Get refund requests involving the seller's products.
     *
     * @authenticated
     *
     * @queryParam status string Filter by status. Example: pending
     * @queryParam per_page integer Results per page. Example: 10
     *
     * @response 200 {"status":true,"message":"Seller refund requests fetched","data":{}}
     */
    public function sellerIndex(Request $request): JsonResponse
    {
        try {
            $seller = \App\Models\Seller::where('user_id', $request->user()->id)->first();
            if (!$seller) {
                return $this->error('Seller account not found', 404);
            }

            $refunds = RefundRequest::where('seller_id', $seller->id)
                ->with([
                    'order:id,order_number,total_amount',
                    'orderItem:id,product_id,quantity,subtotal',
                    'orderItem.product:id,title,slug,image',
                    'user:id,name',
                ])
                ->when($request->status, fn ($q, $s) => $q->where('status', $s))
                ->latest()
                ->paginate(min($request->integer('per_page', 15), 50));

            return $this->success($refunds, 'Seller refund requests fetched');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Unable to fetch seller refund requests.', 500);
        }
    }
}
