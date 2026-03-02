<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDelivery;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * @group Order Keys
 *
 * APIs for buyers to access their purchased license keys
 * and report issues with invalid or already-redeemed keys.
 *
 * ## When Keys Become Available
 *
 * Keys are delivered automatically after payment is confirmed.
 * The typical timeline is:
 *
 * 1. Payment confirmed via webhook → order created
 * 2. `DispatchAutoDelivery` listener assigns keys from the seller's key pool
 * 3. Keys appear in `GET /my-keys` with `status: "delivered"`
 *
 * This usually happens within **seconds** of payment confirmation.
 *
 * ## Retrieving Keys
 *
 * ```
 * GET /my-keys                    → All keys across all orders
 * GET /my-keys/order/{order_id}   → Keys for a specific order
 * ```
 *
 * Each delivery contains a `keys` array with the actual license key strings.
 * Keys are only visible after `status` is `"delivered"`.
 *
 * ## Reporting Bad Keys
 *
 * If a customer receives an invalid or already-redeemed key:
 *
 * ```
 * POST /my-keys/deliveries/{delivery_id}/report
 * body: { key_index: 0, reason: "Key already redeemed" }
 * ```
 *
 * This automatically creates a high-priority support ticket for investigation.
 * The `key_index` refers to the position in the `keys` array (0-based).
 */
class OrderKeyController extends Controller
{
    /**
     * List my keys
     *
     * Get all delivered keys for the authenticated user's completed orders.
     *
     * @authenticated
     *
     * @queryParam order_id integer Filter by specific order. Example: 5
     * @queryParam per_page integer Results per page (default 15). Example: 10
     *
     * @response 200 {"status":true,"message":"Keys fetched","data":{"current_page":1,"data":[],"total":0}}
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $deliveries = OrderDelivery::where('status', 'delivered')
            ->whereHas('orderItem.order', function ($q) use ($user, $request) {
                $q->where('user_id', $user->id);
                if ($request->order_id) {
                    $q->where('id', $request->order_id);
                }
            })
            ->with([
                'orderItem:id,order_id,product_id,seller_offer_id,quantity',
                'orderItem.product:id,title,slug,image',
                'orderItem.order:id,order_number',
            ])
            ->latest()
            ->paginate(min((int) $request->input('per_page', 15), 50));

            $deliveries->getCollection()->transform(fn ($d) => [
                'id'              => $d->id,
                'delivery_method' => $d->delivery_method,
                'keys'            => in_array($d->delivery_method, ['auto', 'auto_key']) ? ($d->payload['keys'] ?? []) : null,
                'status'          => $d->status,
                'delivered_at'    => $d->delivered_at?->toISOString(),
                'product'         => $d->orderItem?->product?->only(['id', 'title', 'slug', 'image']),
                'order'           => [
                    'id'           => $d->orderItem?->order?->id,
                    'order_number' => $d->orderItem?->order?->order_number,
                ],
            ]);

            return $this->success($deliveries, 'Keys fetched');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Unable to fetch keys.', 500);
        }
    }

    /**
     * Show keys for a specific order
     *
     * Get all delivered keys for a specific order the user owns.
     *
     * @authenticated
     *
     * @urlParam order integer required Order ID. Example: 1
     *
     * @response 200 {"status":true,"message":"Order keys fetched","data":[]}
     * @response 403 {"status":false,"message":"Unauthorized"}
     */
    public function show(Request $request, int $order): JsonResponse
    {
        try {
            $orderModel = Order::where('id', $order)
                ->where('user_id', $request->user()->id)
                ->first();

            if (!$orderModel) {
                return $this->error('Order not found', 404);
            }

            $deliveries = OrderDelivery::where('status', 'delivered')
                ->whereHas('orderItem', fn ($q) => $q->where('order_id', $order))
                ->with([
                    'orderItem:id,order_id,product_id,quantity,delivery_status',
                    'orderItem.product:id,title,slug,image',
                ])
                ->get()
                ->map(fn ($d) => [
                    'id'              => $d->id,
                    'delivery_method' => $d->delivery_method,
                    'keys'            => in_array($d->delivery_method, ['auto', 'auto_key']) ? ($d->payload['keys'] ?? []) : null,
                    'status'          => $d->status,
                    'delivered_at'    => $d->delivered_at?->toISOString(),
                    'product'         => $d->orderItem?->product?->only(['id', 'title', 'slug', 'image']),
                ]);

            return $this->success($deliveries, 'Order keys fetched');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Unable to fetch order keys.', 500);
        }
    }

    /**
     * Report a bad key
     *
     * Report a delivered key as invalid or already used.
     * Creates a support ticket automatically for investigation.
     *
     * @authenticated
     *
     * @urlParam delivery integer required Delivery ID. Example: 1
     *
     * @bodyParam key_index integer required Index of the bad key in the keys array. Example: 0
     * @bodyParam reason string required Reason for reporting. Example: Key already redeemed
     *
     * @response 200 {"status":true,"message":"Key reported. A support ticket has been created."}
     * @response 404 {"status":false,"message":"Delivery not found"}
     */
    public function reportKey(Request $request, int $delivery): JsonResponse
    {
        $request->validate([
            'key_index' => 'required|integer|min:0',
            'reason'    => 'required|string|max:500',
        ]);

        try {
            $deliveryModel = OrderDelivery::whereHas('orderItem.order', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            })->find($delivery);

            if (!$deliveryModel) {
                return $this->error('Delivery not found', 404);
            }

            $keys = $deliveryModel->payload['keys'] ?? [];
            $keyIndex = $request->key_index;

            if (!isset($keys[$keyIndex]) || empty($keys[$keyIndex])) {
                return $this->error('Invalid key index', 422);
            }

            $ticket = DB::transaction(function () use ($request, $deliveryModel, $keys, $keyIndex) {
                $ticket = \App\Models\SupportTicket::create([
                    'user_id'    => $request->user()->id,
                    'department' => 'orders',
                    'subject'    => 'Bad Key Report - Order #' . ($deliveryModel->orderItem?->order?->order_number ?? 'N/A'),
                    'priority'   => 'high',
                    'status'     => 'open',
                    'order_id'   => $deliveryModel->orderItem?->order_id,
                    'ip_address' => $request->ip(),
                ]);

                $rawKey = $keys[$keyIndex];
                $maskedKey = strlen($rawKey) > 8
                    ? substr($rawKey, 0, 4) . '***' . substr($rawKey, -4)
                    : '***';

                \App\Models\SupportTicketMessage::create([
                    'support_ticket_id' => $ticket->id,
                    'user_id'           => $request->user()->id,
                    'message'           => "Reported bad key (index #{$keyIndex}): {$maskedKey}\n\nReason: {$request->reason}",
                    'sender_type'       => 'customer',
                ]);

                return $ticket;
            });

            return $this->success([
                'ticket_id' => $ticket->id,
            ], 'Key reported. A support ticket has been created.');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Unable to report key.', 500);
        }
    }
}
