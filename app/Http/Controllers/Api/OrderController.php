<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Order;
use App\Models\OrderNote;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * List all buyer orders (paginated)
     */
    public function index(Request $request)
    {
        $orders = $request->user()
            ->orders()
            ->with(['products.keys', 'invoices.items'])
            ->latest()
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'data'   => $orders
        ]);
    }

    /**
     * Show a single order with all details
     */
    public function show(Request $request, $id)
    {
        $order = $request->user()
            ->orders()
            ->with([
                'buyer',
                'products.keys',
                'transactions',
                'statusHistories.changedBy',
                'notes.user',
                'addresses',
                'invoices.items'
            ])
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $order
        ]);
    }

    /**
     * Create a new order
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'currency' => 'required|string|size:3',
            'items'    => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.offer_id'   => 'nullable|exists:seller_offers,id',
            'items.*.quantity'   => 'required|integer|min:1',

            // optional
            'addresses'          => 'nullable|array',
            'addresses.*.type'   => 'required_with:addresses|in:billing,shipping',
            'addresses.*.full_name' => 'required_with:addresses|string|max:255',
            'addresses.*.email'     => 'nullable|email',
            'addresses.*.phone'     => 'nullable|string|max:50',
            'addresses.*.address_line1' => 'required_with:addresses|string|max:255',
            'addresses.*.city'     => 'required_with:addresses|string|max:100',
            'addresses.*.country'  => 'required_with:addresses|string|size:2',

            'note' => 'nullable|string',
        ]);

        $currency = Currency::where('code', strtoupper($validated['currency']))
            ->where('is_active', true)
            ->firstOrFail();

        try {
            $order = $this->orderService->createOrder([
                'buyer_id'        => $request->user()->id,
                'buyer_name'      => $request->user()->name ?? null,
                'buyer_email'     => $request->user()->email ?? null,
                'currency_code'   => $currency->code,
                'currency_symbol' => $currency->symbol,
                'created_by'      => $request->user()->id,

                'products'  => $validated['items'],
                'addresses' => $validated['addresses'] ?? [],
                'note'      => $validated['note'] ?? null,
                'note_private' => false,
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Order created successfully',
                'data'    => $order,
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Order creation failed',
                'error'   => config('app.debug') ? $e->getMessage() : 'Something went wrong',
            ], 500);
        }
    }

    /**
     * Update order status (admin/seller action)
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending','processing','delivered','completed','refunded','cancelled'])],
        ]);

        $order = Order::findOrFail($id);

        $this->orderService->updateStatus($order, $validated['status'], $request->user()->id);

        return response()->json([
            'status'  => 'success',
            'message' => "Order status updated to {$validated['status']}",
            'data'    => $order->fresh(['statusHistories']),
        ]);
    }

    /**
     * Mark order as paid (add transaction + update invoice)
     */
    public function markAsPaid(Request $request, $id)
    {
        $validated = $request->validate([
            'gateway'        => 'required|string',
            'transaction_id' => 'nullable|string',
            'amount'         => 'required|numeric|min:0',
        ]);

        $order = Order::findOrFail($id);

        $this->orderService->markAsPaid($order, $validated, $request->user()->id);

        return response()->json([
            'status'  => 'success',
            'message' => 'Order marked as paid',
            'data'    => $order->fresh(['transactions','invoices']),
        ]);
    }

    /**
     * Add a note to an order
     */
    public function addNote(Request $request, $id)
    {
        $validated = $request->validate([
            'note'       => 'required|string',
            'is_private' => 'boolean',
        ]);

        $order = Order::findOrFail($id);

        OrderNote::create([
            'order_id'   => $order->id,
            'user_id'    => $request->user()->id,
            'note'       => $validated['note'],
            'is_private' => $validated['is_private'] ?? true,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Note added to order',
            'data'    => $order->fresh(['notes.user']),
        ]);
    }

    /**
     * Refund an order
     */
    public function refund(Request $request, $id)
    {
        $validated = $request->validate([
            'amount'         => 'required|numeric|min:0',
            'gateway'        => 'required|string',
            'transaction_id' => 'nullable|string',
            'note'           => 'nullable|string',
        ]);

        $order = Order::findOrFail($id);

        $this->orderService->refundOrder($order, $validated, $request->user()->id);

        return response()->json([
            'status'  => 'success',
            'message' => 'Order refunded successfully',
            'data'    => $order->fresh(['transactions','statusHistories','invoices']),
        ]);
    }
}
