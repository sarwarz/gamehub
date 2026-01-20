<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderAddress;
use App\Models\OrderDelivery;
use App\Models\SellerOffer;
use App\Models\SellerEarning;
use App\Models\Transaction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    /**
     * Create Order (Checkout)
     *
     * Create a new order for the authenticated user.
     * This endpoint validates seller offers, creates the order,
     * billing address, order items, seller earnings, and a payment transaction.
     *
     * @group Orders
     * @authenticated
     *
     * @bodyParam currency string required Currency code. Example: USD
     * @bodyParam payment_method string required Payment method. Example: stripe
     *
     * @bodyParam billing object required Billing address details.
     * @bodyParam billing.name string required Customer name. Example: John Doe
     * @bodyParam billing.email string required Email address. Example: john@example.com
     * @bodyParam billing.phone string required Phone number. Example: +8801712345678
     * @bodyParam billing.address string required Street address.
     * @bodyParam billing.city string required City name.
     * @bodyParam billing.country string required Country code. Example: BD
     * @bodyParam billing.postcode string required Postal code.
     *
     * @bodyParam items array required List of order items.
     * @bodyParam items[].seller_offer_id integer required Seller offer ID. Example: 15
     * @bodyParam items[].quantity integer required Quantity. Example: 2
     *
     * @response 201 {
     *  "success": true,
     *  "message": "Order created successfully.",
     *  "data": {
     *      "order_id": 101,
     *      "order_number": "ORD-20260120-0001",
     *      "status": "pending",
     *      "payment_status": "pending"
     *  }
     * }
     *
     * @response 422 {
     *  "message": "One or more seller offers are not available."
     * }
     *
     * @response 500 {
     *  "success": false,
     *  "message": "Unable to create order. Please try again."
     * }
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            $order = DB::transaction(function () use ($request) {

                $user  = $request->user();
                $items = $request->items;

                $subtotal   = 0;
                $orderItems = [];

                // STEP 1: Validate seller offers & calculate totals
                foreach ($items as $item) {

                    $offer = SellerOffer::lockForUpdate()->findOrFail($item['seller_offer_id']);

                    if ($offer->status !== 'active') {
                        abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'One or more seller offers are not available.');
                    }

                    $unitPrice = $offer->retail_price;
                    $lineTotal = bcmul($unitPrice, $item['quantity'], 2);

                    $subtotal = bcadd($subtotal, $lineTotal, 2);

                    $orderItems[] = compact('offer', 'unitPrice', 'lineTotal') + [
                        'quantity' => $item['quantity'],
                    ];
                }

                $taxAmount      = 0;
                $discountAmount = 0;

                $totalAmount = bcadd(
                    bcadd($subtotal, $taxAmount, 2),
                    -$discountAmount,
                    2
                );

                $customMeta = $request->input('meta', []);

                // STEP 2: Create Order
               $order = Order::create([
                    'user_id'          => $user->id,
                    'currency'         => $request->currency,

                    'subtotal'         => $subtotal,
                    'tax_amount'       => $taxAmount,
                    'discount_amount'  => $discountAmount,
                    'total_amount'     => $totalAmount,

                    'payment_method'   => $request->payment_method,
                    'payment_reference'=> null, // filled after gateway success
                    'payment_status'   => 'pending',

                    'status'           => 'pending',

                    'meta' => array_merge( [
                        'client' => [
                            'ip'         => $request->ip(),
                            'user_agent' => $request->userAgent(),
                            'platform'   => $request->header('X-Platform', 'web'),
                        ],
                        'checkout' => [
                            'source'  => 'api',
                            'version' => 'v1',
                        ],
                        'flags' => [
                            'guest_checkout' => false,
                            'auto_delivery'  => true,
                        ],
                    ], $customMeta),

                ]);

                // STEP 3: Billing Address
                OrderAddress::create([
                    'order_id' => $order->id,
                    'type'     => 'billing',
                    ...$request->billing,
                ]);

                // STEP 4: Order Items, Deliveries & Earnings
                foreach ($orderItems as $data) {

                    $offer = $data['offer'];

                    $orderItem = OrderItem::create([
                        'order_id'        => $order->id,
                        'seller_id'       => $offer->seller_id,
                        'product_id'      => $offer->product_id,
                        'seller_offer_id' => $offer->id,
                        'quantity'        => $data['quantity'],
                        'unit_price'      => $data['unitPrice'],
                        'subtotal'        => $data['lineTotal'],
                        'delivery_type'   => 'auto',
                        'delivery_status' => 'pending',
                        'status'          => 'active',
                    ]);

                    OrderDelivery::create([
                        'order_item_id'   => $orderItem->id,
                        'delivery_method' => 'auto',
                        'status'          => 'pending',
                    ]);

                    $commission = round($data['lineTotal'] * 0.10, 2);
                    $netAmount  = $data['lineTotal'] - $commission;

                    SellerEarning::create([
                        'seller_id'     => $offer->seller_id,
                        'order_id'      => $order->id,
                        'order_item_id' => $orderItem->id,
                        'gross_amount'  => $data['lineTotal'],
                        'commission'    => $commission,
                        'net_amount'    => $netAmount,
                        'status'        => 'pending',
                    ]);
                }

                // STEP 5: Transaction
                Transaction::create([
                    'user_id'         => $user->id,
                    'reference_type'  => Order::class,
                    'reference_id'    => $order->id,
                    'trx'             => uniqid('trx_'),
                    'amount'          => $order->total_amount,
                    'fee'             => 0,
                    'net_amount'      => $order->total_amount,
                    'currency'        => $order->currency,
                    'type'            => 'debit',
                    'category'        => 'order',
                    'status'          => 'pending',
                    'payment_method'  => $request->payment_method,
                ]);

                return $order;
            });

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully.',
                'data' => [
                    'order_id'      => $order->id,
                    'order_number'  => $order->order_number,
                    'status'        => $order->status,
                    'payment_status'=> $order->payment_status,
                ],
            ], Response::HTTP_CREATED);

        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Unable to create order. Please try again.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * List User Orders
     *
     * Get a paginated list of orders for the authenticated user.
     *
     * @group Orders
     * @authenticated
     *
     * @response 200 {
     *  "success": true,
     *  "data": {
     *      "current_page": 1,
     *      "data": []
     *  }
     * }
     */
    public function index(): JsonResponse
    {
        $orders = auth()->user()
            ->orders()
            ->with('items.product')
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data'    => $orders,
        ]);
    }

    /**
     * Show Order Details
     *
     * Get full details of a specific order.
     *
     * @group Orders
     * @authenticated
     *
     * @urlParam order integer required Order ID. Example: 101
     *
     * @response 200 {
     *  "success": true,
     *  "data": {
     *      "id": 101,
     *      "status": "pending"
     *  }
     * }
     *
     * @response 403 {
     *  "message": "This action is unauthorized."
     * }
     */
    public function show(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        return response()->json([
            'success' => true,
            'data'    => $order->load([
                'items.product',
                'items.offer',
                'items.deliveries',
                'transactions',
            ]),
        ]);
    }
}
