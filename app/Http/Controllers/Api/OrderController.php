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
     * List buyer orders
     *
     * Returns paginated orders belonging to the authenticated buyer.
     *
     * @group Orders
     * @authenticated
     *
     * @queryParam page integer Page number. Example: 1
     *
     * @response 200 {
     *   "status": "success",
     *   "data": {
     *     "current_page": 1,
     *     "data": []
     *   }
     * }
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
     * Get order details
     *
     * Returns full details of a single order owned by the authenticated user.
     *
     * @group Orders
     * @authenticated
     *
     * @urlParam id integer required Order ID. Example: 101
     *
     * @response 200 {
     *   "status": "success",
     *   "data": {
     *     "id": 101,
     *     "status": "pending"
     *   }
     * }
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
     *
     * Creates a new order with products, currency, addresses, and optional note.
     *
     * @group Orders
     * @authenticated
     *
     * @bodyParam currency string required Currency code (ISO-3). Example: USD
     * @bodyParam items array required Order items.
     * @bodyParam items[].product_id integer required Product ID. Example: 10
     * @bodyParam items[].offer_id integer Optional Seller offer ID. Example: 5
     * @bodyParam items[].quantity integer required Quantity. Example: 2
     *
     * @bodyParam addresses array Optional Billing or shipping addresses.
     * @bodyParam addresses[].type string Example: billing
     * @bodyParam addresses[].full_name string Example: John Doe
     * @bodyParam addresses[].address_line1 string Example: 123 Main Street
     * @bodyParam addresses[].city string Example: New York
     * @bodyParam addresses[].country string Example: US
     *
     * @bodyParam note string Optional Order note.
     *
     * @response 201 {
     *   "status": "success",
     *   "message": "Order created successfully"
     * }
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'currency' => 'required|string|size:3',
            'items'    => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.offer_id'   => 'nullable|exists:seller_offers,id',
            'items.*.quantity'   => 'required|integer|min:1',

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
                'products'        => $validated['items'],
                'addresses'       => $validated['addresses'] ?? [],
                'note'            => $validated['note'] ?? null,
                'note_private'    => false,
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
            ], 500);
        }
    }

    /**
     * Update order status
     *
     * Updates the status of an order (admin or seller action).
     *
     * @group Orders
     * @authenticated
     *
     * @urlParam id integer required Order ID. Example: 101
     * @bodyParam status string required New status. Example: completed
     *
     * @response 200 {
     *   "status": "success",
     *   "message": "Order status updated"
     * }
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([
                'pending','processing','delivered',
                'completed','refunded','cancelled'
            ])],
        ]);

        $order = Order::findOrFail($id);

        $this->orderService->updateStatus(
            $order,
            $validated['status'],
            $request->user()->id
        );

        return response()->json([
            'status'  => 'success',
            'message' => "Order status updated to {$validated['status']}",
            'data'    => $order->fresh(['statusHistories']),
        ]);
    }

    /**
     * Mark order as paid
     *
     * Adds a payment transaction and updates invoice status.
     *
     * @group Orders
     * @authenticated
     *
     * @urlParam id integer required Order ID. Example: 101
     * @bodyParam gateway string required Payment gateway. Example: stripe
     * @bodyParam amount number required Paid amount. Example: 49.99
     *
     * @response 200 {
     *   "status": "success",
     *   "message": "Order marked as paid"
     * }
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
     * Add order note
     *
     * Adds a private or public note to an order.
     *
     * @group Orders
     * @authenticated
     *
     * @urlParam id integer required Order ID. Example: 101
     * @bodyParam note string required Note content.
     * @bodyParam is_private boolean Optional Private note flag.
     *
     * @response 200 {
     *   "status": "success",
     *   "message": "Note added to order"
     * }
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
     * Refund order
     *
     * Refunds an order and records the refund transaction.
     *
     * @group Orders
     * @authenticated
     *
     * @urlParam id integer required Order ID. Example: 101
     * @bodyParam amount number required Refund amount. Example: 20
     * @bodyParam gateway string required Payment gateway. Example: stripe
     *
     * @response 200 {
     *   "status": "success",
     *   "message": "Order refunded successfully"
     * }
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

        $this->orderService->refundOrder(
            $order,
            $validated,
            $request->user()->id
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Order refunded successfully',
            'data'    => $order->fresh([
                'transactions',
                'statusHistories',
                'invoices'
            ]),
        ]);
    }
}
