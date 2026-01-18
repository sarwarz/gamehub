<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Order;
use App\Models\OrderNote;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

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

    public function store(Request $request, OrderService $orderService)
    {
        try {
            /**
             * 1️⃣ Validate request
             */
            $validated = $request->validate([
                'currency' => 'required|string|size:3',
                'items' => 'required|array|min:1',
                'items.*.seller_offer_id' => 'required|exists:seller_offers,id',
                'items.*.quantity' => 'required|integer|min:1',
            ]);

            /**
             * 2️⃣ Validate currency
             */
            $currency = Currency::where('code', strtoupper($validated['currency']))
                ->where('is_active', true)
                ->first();

            if (!$currency) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Invalid or inactive currency',
                ], 422);
            }

            /**
             * 3️⃣ Create order using service
             */
            $order = $orderService->create(
                $request->user(),
                $validated['items'],
                $currency->code
            );

            /**
             * 4️⃣ Save addresses (optional)
             */
            if (!empty($validated['addresses'])) {
                foreach ($validated['addresses'] as $address) {
                    $order->addresses()->create($address);
                }
            }

            /**
             * 5️⃣ Attach note (optional)
             */
            if (!empty($validated['note'])) {
                $order->update([
                    'meta' => array_merge($order->meta ?? [], [
                        'note' => $validated['note']
                    ])
                ]);
            }

            /**
             * 6️⃣ Success response
             */
            return response()->json([
                'status'  => 'success',
                'message' => 'Order created successfully',
                'data'    => [
                    'order_id'     => $order->id,
                    'order_number' => $order->order_number,
                    'currency'     => $order->currency,
                    'total_amount' => $order->total_amount,
                    'status'       => $order->status,
                ],
            ], 201);

        } catch (ValidationException $e) {

            // ❌ Validation / stock / business rule errors
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);

        } catch (HttpException $e) {

            // ❌ Explicit HTTP exceptions
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], $e->getStatusCode());

        } catch (\Throwable $e) {

            // ❌ System / unexpected errors
            \Log::error('Order creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Order creation failed. Please try again.',
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
        
    }

    
}
