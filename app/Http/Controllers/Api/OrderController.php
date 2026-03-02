<?php

namespace App\Http\Controllers\Api;

use App\Models\Tax;
use App\Models\Order;
use App\Models\Currency;
use App\Models\Seller;
use App\Models\OrderItem;
use App\Models\OrderNote;
use App\Models\Transaction;
use App\Models\SellerOffer;
use App\Models\OrderAddress;
use App\Models\OrderDelivery;
use App\Models\SellerEarning;
use App\Services\CouponService;
use App\Services\WalletService;
use App\Services\InvoiceService;
use App\Services\SellerBalanceService;
use App\Jobs\AutoDeliverOrderJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Services\OrderNotificationService;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderDetailResource;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * @group Orders
 *
 * APIs for managing customer orders after checkout is complete.
 * Orders are created automatically when payment is confirmed via webhook
 * (see **Checkout** and **Payment Webhooks** sections).
 *
 * ## Order Lifecycle
 *
 * ```
 * pending → processing → completed
 *    ↓                      ↓
 * cancelled              refunded (partial/full)
 * ```
 *
 * | Status       | Meaning |
 * |--------------|---------|
 * | `pending`    | Order created, payment not yet confirmed (legacy v1 flow only) |
 * | `processing` | Payment confirmed, keys being delivered |
 * | `completed`  | All keys delivered to the customer |
 * | `cancelled`  | Cancelled by customer or admin (wallet refunded if applicable) |
 * | `refunded`   | Refund issued after completion |
 *
 * ## Payment Statuses
 *
 * | Status    | Meaning |
 * |-----------|---------|
 * | `pending` | Awaiting payment (legacy flow) |
 * | `paid`    | Payment confirmed |
 * | `failed`  | Payment attempt failed |
 * | `refunded`| Full or partial refund issued |
 *
 * ## Post-Purchase Flow (Next.js)
 *
 * After checkout completes, use these endpoints:
 *
 * ```
 * 1. GET /my-orders                    → List all orders (with filters)
 * 2. GET /orders/{id}                  → Full order details (items, addresses, transactions)
 * 3. GET /orders/{id}/track            → Step-by-step timeline + per-item delivery status
 * 4. GET /my-keys/order/{id}           → Retrieve delivered license keys
 * 5. GET /orders/{id}/invoice          → Get invoice with PDF download link
 * 6. POST /orders/{id}/cancel          → Cancel a pending order
 * 7. GET /orders/{id}/reorder          → Pre-fill checkout from a previous order
 * ```
 *
 * ## What Happens After Payment
 *
 * When a payment webhook is processed, the system automatically:
 * 1. Creates the order with `status: "processing"`, `payment_status: "paid"`
 * 2. Records seller earnings (with commission deduction)
 * 3. Assigns reserved license keys to the order items
 * 4. Generates an invoice (PDF)
 * 5. Dispatches auto-delivery (keys delivered to order items)
 * 6. Sends notifications to customer, seller(s), and admin
 * 7. Increments coupon usage (if a coupon was applied)
 *
 * Once all keys are delivered, the order status changes to `completed`.
 */
class OrderController extends Controller
{
    use AuthorizesRequests;

    /**
     * Create order (Checkout)
     *
     * Place a new order. Validates stock availability, applies taxes based on
     * billing address, validates and applies coupon discounts, then processes
     * payment via wallet or gateway. If fully paid by wallet, the order is
     * immediately confirmed and auto-delivery is triggered.
     *
     * @authenticated
     *
     * @bodyParam currency string required Currency code (ISO 4217). Example: USD
     * @bodyParam payment_method string required Payment method. Example: stripe
     * @bodyParam coupon_code string optional Coupon code to apply discount. Example: SAVE20
     * @bodyParam use_wallet boolean optional Use wallet balance for partial payment. Example: false
     *
     * @bodyParam items array required List of items to purchase.
     * @bodyParam items[].seller_offer_id integer required Seller offer ID. Example: 5
     * @bodyParam items[].quantity integer required Quantity (1-100). Example: 2
     *
     * @bodyParam billing object required Billing address.
     * @bodyParam billing.name string required Full name. Example: John Doe
     * @bodyParam billing.email string required Email. Example: john@example.com
     * @bodyParam billing.phone string optional Phone number. Example: +1234567890
     * @bodyParam billing.address string required Street address. Example: 123 Main St
     * @bodyParam billing.city string required City. Example: New York
     * @bodyParam billing.state string optional State/Province. Example: NY
     * @bodyParam billing.country string required Country code. Example: US
     * @bodyParam billing.postcode string optional Postal code. Example: 10001
     *
     * @response 201 {"status":true,"message":"Order created successfully.","data":{"order_id":1,"order_number":"000001","status":"pending","payment_status":"pending","subtotal":29.99,"tax_amount":2.40,"discount_amount":5.00,"total_amount":27.39}}
     * @response 422 {"status":false,"message":"Insufficient stock for \"Windows 11 Pro\". Available: 3, Requested: 5"}
     * @response 422 {"status":false,"message":"Invalid or expired coupon code."}
     * @response 422 {"status":false,"message":"Insufficient wallet balance."}
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            $order = DB::transaction(function () use ($request) {

                $user  = $request->user();
                $items = $request->items;

                $subtotal   = 0;
                $orderItems = [];
                $productIds = [];
                $categoryIds = [];

                foreach ($items as $item) {

                    $offer = SellerOffer::lockForUpdate()->findOrFail($item['seller_offer_id']);

                    if ($offer->status !== 'active') {
                        abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'One or more seller offers are not available.');
                    }

                    $availableStock = $offer->keys()
                        ->where('status', 'available')
                        ->lockForUpdate()
                        ->count();

                    if ($availableStock < $item['quantity']) {
                        $product = $offer->product;
                        abort(
                            Response::HTTP_UNPROCESSABLE_ENTITY,
                            "Insufficient stock for \"{$product->title}\". Available: {$availableStock}, Requested: {$item['quantity']}"
                        );
                    }

                    $unitPrice = $offer->resolveUnitPrice($item['quantity']);
                    $lineTotal = bcmul($unitPrice, $item['quantity'], 2);
                    $subtotal  = bcadd($subtotal, $lineTotal, 2);

                    $productIds[] = $offer->product_id;
                    $itemCategoryIds = [];
                    if ($offer->product && $offer->product->categories) {
                        foreach ($offer->product->categories as $cat) {
                            $categoryIds[] = $cat->id;
                            $itemCategoryIds[] = $cat->id;
                        }
                    }

                    $orderItems[] = compact('offer', 'unitPrice', 'lineTotal') + [
                        'quantity'     => $item['quantity'],
                        'category_ids' => $itemCategoryIds,
                    ];
                }

                // --- Tax Calculation ---
                $taxAmount = 0;
                $taxDetails = [];
                $billingCountry = $request->input('billing.country');
                $billingState   = $request->input('billing.state');
                $billingCity    = $request->input('billing.city');

                if ($billingCountry) {
                    $taxes = Tax::where('is_active', true)
                        ->where(function ($q) {
                            $q->whereNull('seller_id');
                        })
                        ->where('country', $billingCountry)
                        ->when($billingState, fn ($q) => $q->where(function ($q) use ($billingState) {
                            $q->whereNull('state')->orWhere('state', '')->orWhere('state', $billingState);
                        }))
                        ->when($billingCity, fn ($q) => $q->where(function ($q) use ($billingCity) {
                            $q->whereNull('city')->orWhere('city', '')->orWhere('city', $billingCity);
                        }))
                        ->orderBy('priority')
                        ->get();

                    foreach ($taxes as $tax) {
                        $base = $tax->is_compound ? bcadd($subtotal, $taxAmount, 2) : $subtotal;

                        $amount = $tax->type === 'percent'
                            ? round($base * $tax->rate / 100, 2)
                            : $tax->rate;

                        $taxAmount = bcadd($taxAmount, $amount, 2);
                        $taxDetails[] = [
                            'name' => $tax->name,
                            'rate' => $tax->rate,
                            'type' => $tax->type,
                            'amount' => $amount,
                        ];
                    }
                }

                // --- Coupon Discount ---
                $discountAmount = 0;
                $couponData = null;

                if ($request->filled('coupon_code')) {
                    $couponItems = array_map(fn ($oi) => [
                        'product_id'      => $oi['offer']->product_id,
                        'seller_offer_id' => $oi['offer']->id,
                        'quantity'        => $oi['quantity'],
                        'unit_price'      => (float) $oi['unitPrice'],
                        'line_total'      => (float) $oi['lineTotal'],
                        'category_ids'    => $oi['category_ids'] ?? [],
                    ], $orderItems);

                    $couponService = app(CouponService::class);
                    $couponResult = $couponService->validate(
                        $request->coupon_code,
                        (float) $subtotal,
                        $couponItems,
                        $user->id,
                    );

                    if (!$couponResult['valid']) {
                        abort(Response::HTTP_UNPROCESSABLE_ENTITY, $couponResult['error']);
                    }

                    $discountAmount = $couponResult['discount'];
                    $couponService->incrementUsage($couponResult['coupon']);
                    $couponData = $couponResult['coupon_data'];
                }

                $baseTotalAmount = max(0, bcadd(bcsub($subtotal, $discountAmount, 2), $taxAmount, 2));

                // Resolve exchange rate for multi-currency
                $defaultCurrency = Currency::where('is_default', true)->first();
                $baseCurrencyCode = $defaultCurrency->code ?? 'USD';
                $exchangeRate = 1.00000000;
                $customerCurrency = strtoupper($request->currency ?? $baseCurrencyCode);

                if ($customerCurrency !== strtoupper($baseCurrencyCode)) {
                    $targetCurrency = Currency::where('code', $customerCurrency)
                        ->where('is_active', true)
                        ->first();

                    if (!$targetCurrency) {
                        abort(Response::HTTP_UNPROCESSABLE_ENTITY, "Currency {$customerCurrency} is not supported.");
                    }
                    $exchangeRate = (float) $targetCurrency->rate;
                }

                $baseSubtotal       = $subtotal;
                $baseTaxAmount      = $taxAmount;
                $baseDiscountAmount = $discountAmount;

                $convertedSubtotal       = round($baseSubtotal * $exchangeRate, 2);
                $convertedTaxAmount      = round($baseTaxAmount * $exchangeRate, 2);
                $convertedDiscountAmount = round($baseDiscountAmount * $exchangeRate, 2);
                $convertedTotalAmount    = max(0, round($baseTotalAmount * $exchangeRate, 2));

                $allowedMetaKeys = ['notes', 'gift_message', 'referral'];
                $customMeta = collect($request->input('meta', []))->only($allowedMetaKeys)->toArray();

                $order = Order::create([
                    'user_id'              => $user->id,
                    'currency'             => $customerCurrency,
                    'base_currency'        => $baseCurrencyCode,
                    'base_subtotal'        => $baseSubtotal,
                    'base_tax_amount'      => $baseTaxAmount,
                    'base_discount_amount' => $baseDiscountAmount,
                    'base_total_amount'    => $baseTotalAmount,
                    'exchange_rate'        => $exchangeRate,
                    'subtotal'             => $convertedSubtotal,
                    'tax_amount'           => $convertedTaxAmount,
                    'discount_amount'      => $convertedDiscountAmount,
                    'total_amount'         => $convertedTotalAmount,
                    'payment_method'       => $request->payment_method,
                    'payment_reference'    => null,
                    'payment_status'       => 'pending',
                    'status'               => 'pending',
                    'meta' => array_merge($customMeta, [
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
                            'guest_checkout' => (bool) \App\Models\Setting::get('store', 'guest_checkout', false),
                            'auto_delivery'  => true,
                        ],
                        'tax_details'  => $taxDetails,
                        'coupon'       => $couponData,
                    ]),
                ]);

                $order->notes()->create([
                    'user_id' => $user->id,
                    'note'    => 'Order created via API checkout.',
                    'type'    => 'system',
                    'is_visible_to_customer' => false,
                ]);

                OrderAddress::create([
                    'order_id' => $order->id,
                    'type'     => 'billing',
                    ...$request->billing,
                ]);

                foreach ($orderItems as $data) {

                    $offer = $data['offer'];
                    $convertedUnitPrice = round($data['unitPrice'] * $exchangeRate, 2);
                    $convertedLineTotal = round($data['lineTotal'] * $exchangeRate, 2);

                    $orderItem = OrderItem::create([
                        'order_id'        => $order->id,
                        'seller_id'       => $offer->seller_id,
                        'product_id'      => $offer->product_id,
                        'seller_offer_id' => $offer->id,
                        'quantity'        => $data['quantity'],
                        'unit_price'      => $convertedUnitPrice,
                        'subtotal'        => $convertedLineTotal,
                        'delivery_type'   => 'auto',
                        'delivery_status' => 'pending',
                        'status'          => 'active',
                    ]);

                    OrderDelivery::create([
                        'order_item_id'   => $orderItem->id,
                        'delivery_method' => 'auto',
                        'status'          => 'pending',
                    ]);

                    // Seller earnings always in base currency
                    $commissionRate = SellerBalanceService::getCommissionRate($offer->product);
                    $commission = round($data['lineTotal'] * ($commissionRate / 100), 2);
                    $netAmount  = bcsub($data['lineTotal'], $commission, 2);

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

                $walletAmountPaid = 0;
                $gatewayAmountDue = $convertedTotalAmount;
                $isFullWalletPay  = false;
                $paymentMethod    = $request->payment_method;

                if ($paymentMethod === 'wallet' || $request->boolean('use_wallet')) {

                    $walletService = app(WalletService::class);
                    $wallet = $walletService->getOrCreateWallet($user);
                    $walletService->ensureWalletUsable($wallet);

                    if ($paymentMethod === 'wallet') {
                        if ($wallet->balance < $convertedTotalAmount) {
                            abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Insufficient wallet balance.');
                        }

                        $walletAmountPaid = (float) $convertedTotalAmount;
                        $gatewayAmountDue = 0;
                        $isFullWalletPay  = true;
                    } else {
                        $settings = $walletService->settings();
                        if (!$settings->partial_payment_enabled) {
                            abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Partial wallet payment is not enabled.');
                        }

                        $walletAmountPaid = min((float) $wallet->balance, (float) $convertedTotalAmount);
                        $gatewayAmountDue = bcsub($convertedTotalAmount, $walletAmountPaid, 2);
                    }

                    if ($walletAmountPaid > 0) {
                        $walletService->payForOrder($user, $order->id, $walletAmountPaid);

                        $order->notes()->create([
                            'user_id' => $user->id,
                            'note'    => "Wallet payment of {$walletAmountPaid} {$request->currency} applied.",
                            'type'    => 'system',
                            'is_visible_to_customer' => true,
                        ]);
                    }
                }

                $order->update([
                    'meta' => array_merge($order->meta ?? [], [
                        'wallet' => [
                            'wallet_amount'   => $walletAmountPaid,
                            'gateway_amount'  => $gatewayAmountDue,
                            'full_wallet_pay' => $isFullWalletPay,
                        ],
                    ]),
                ]);

                $trx = 'TRX-' . now()->format('ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(6));

                Transaction::create([
                    'user_id'        => $user->id,
                    'reference_type' => Order::class,
                    'reference_id'   => $order->id,
                    'trx'            => $trx,
                    'amount'         => $order->total_amount,
                    'fee'            => 0,
                    'net_amount'     => $order->total_amount,
                    'currency'       => $order->currency,
                    'type'           => 'debit',
                    'category'       => 'order',
                    'status'         => $isFullWalletPay ? 'completed' : 'pending',
                    'payment_method' => $paymentMethod,
                ]);

                if ($isFullWalletPay) {
                    $order->update([
                        'payment_status' => 'paid',
                        'status'         => 'processing',
                        'paid_at'        => now(),
                    ]);

                    app(SellerBalanceService::class)->onPaymentConfirmed($order->id);
                    app(InvoiceService::class)->generateFromOrder($order->fresh());
                }

                return $order;
            });

            OrderNotificationService::orderPlaced($order);

            if ($order->payment_status === 'paid') {
                OrderNotificationService::paymentConfirmed($order);
                dispatch(new AutoDeliverOrderJob($order->id));
            }

            $responseData = [
                'order_id'       => $order->id,
                'order_number'   => $order->order_number,
                'status'         => $order->status,
                'payment_status' => $order->payment_status,
                'subtotal'       => (float) $order->subtotal,
                'tax_amount'     => (float) $order->tax_amount,
                'discount_amount'=> (float) $order->discount_amount,
                'total_amount'   => (float) $order->total_amount,
            ];

            $walletMeta = $order->meta['wallet'] ?? null;
            if ($walletMeta && $walletMeta['gateway_amount'] > 0) {
                $responseData['wallet_amount_paid'] = $walletMeta['wallet_amount'];
                $responseData['gateway_amount_due'] = $walletMeta['gateway_amount'];
            }

            return $this->success($responseData, 'Order created successfully.', Response::HTTP_CREATED);

        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return $this->error($e->getMessage(), $e->getStatusCode());
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Unable to create order. Please try again.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * List my orders
     *
     * Get a paginated list of orders for the authenticated user.
     * Supports filtering by order status and payment status.
     *
     * @authenticated
     *
     * @queryParam status string Filter by order status. Example: pending
     * @queryParam payment_status string Filter by payment status. Example: paid
     * @queryParam per_page integer Results per page (default 15, max 50). Example: 10
     *
     * @response 200 {"status":true,"message":"Success","data":{"current_page":1,"data":[],"total":0}}
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = min((int) $request->input('per_page', 15), 50);

            $orders = auth()->user()
                ->orders()
                ->with('items.product:id,title,slug,image')
                ->when($request->status, fn ($q) => $q->where('status', $request->status))
                ->when($request->payment_status, fn ($q) => $q->where('payment_status', $request->payment_status))
                ->latest()
                ->paginate($perPage);

            return $this->success($orders);
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Unable to fetch orders.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Show order details
     *
     * Get full details of a specific order including items, deliveries,
     * transactions, billing address, wallet breakdown, tax details, and coupon info.
     *
     * @authenticated
     *
     * @urlParam order integer required The order ID. Example: 1
     *
     * @response 200 {"status":true,"message":"Success","data":{"order":{"id":1,"order_number":"000001","status":"pending"},"items":[],"transactions":[],"addresses":{}}}
     * @response 403 {"status":false,"message":"You are not authorized to view this order."}
     */
    public function show(Order $order): JsonResponse
    {
        try {
            if ($order->user_id !== auth()->id()) {
                return $this->error('You are not authorized to view this order.', Response::HTTP_FORBIDDEN);
            }

            $order->load([
                'items.product:id,title,slug,image',
                'items.offer:id,retail_price',
                'items.deliveries',
                'transactions',
                'addresses',
            ]);

            return $this->success(new OrderDetailResource($order));
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Unable to fetch order details.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Cancel order
     *
     * Cancel a pending order. Refunds wallet balance if wallet was used,
     * reverts seller earnings, reverses the transaction, and restores
     * coupon usage if a coupon was applied. Only pending orders can be cancelled.
     *
     * @authenticated
     *
     * @urlParam order integer required The order ID. Example: 1
     *
     * @response 200 {"status":true,"message":"Order cancelled successfully."}
     * @response 403 {"status":false,"message":"You are not authorized to cancel this order."}
     * @response 422 {"status":false,"message":"Only pending orders can be cancelled."}
     */
    public function cancel(Order $order): JsonResponse
    {
        if ($order->user_id !== auth()->id()) {
            return $this->error('You are not authorized to cancel this order.', Response::HTTP_FORBIDDEN);
        }

        if (!in_array($order->status, ['pending'])) {
            return $this->error('Only pending orders can be cancelled.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            DB::transaction(function () use ($order) {
                $order = Order::where('id', $order->id)->lockForUpdate()->first();

                if ($order->status !== 'pending') {
                    throw new \RuntimeException('Order is no longer pending.');
                }

                $order->update([
                    'status'       => 'cancelled',
                    'cancelled_at' => now(),
                ]);

                $order->items()->update(['status' => 'cancelled']);

                // Refund wallet if wallet was used
                $walletMeta = $order->meta['wallet'] ?? null;
                if ($walletMeta && $walletMeta['wallet_amount'] > 0) {
                    $walletService = app(WalletService::class);
                    $walletService->refundToWallet(
                        $order->user,
                        $order->id,
                        $walletMeta['wallet_amount'],
                        'Order cancelled — wallet refund'
                    );
                }

                // Revert seller earnings
                SellerEarning::where('order_id', $order->id)
                    ->where('status', 'pending')
                    ->delete();

                // Mark transaction as reversed
                Transaction::where('reference_type', Order::class)
                    ->where('reference_id', $order->id)
                    ->where('status', 'pending')
                    ->update(['status' => 'reversed']);

                $couponData = $order->meta['coupon'] ?? null;
                if ($couponData && !empty($couponData['coupon_id'])) {
                    app(CouponService::class)->decrementUsage($couponData['coupon_id']);
                }

                $order->notes()->create([
                    'user_id' => auth()->id(),
                    'note'    => 'Order cancelled by customer.',
                    'type'    => 'system',
                    'is_visible_to_customer' => true,
                ]);
            });

            OrderNotificationService::orderCancelled($order);

            return $this->success(null, 'Order cancelled successfully.');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to cancel order.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Track order
     *
     * Get order tracking information with a step-by-step timeline
     * (placed → paid → processing → delivered) and per-item delivery status.
     * Delivery payload (license keys) is only exposed once delivery is complete.
     *
     * @authenticated
     *
     * @urlParam order integer required The order ID. Example: 1
     *
     * @response 200 {"status":true,"message":"Success","data":{"order_number":"000001","status":"processing","payment_status":"paid","timeline":[{"status":"order_placed","label":"Order Placed","completed":true,"date":"2026-02-28T00:00:00.000Z"}],"items":[]}}
     * @response 403 {"status":false,"message":"You are not authorized to track this order."}
     */
    public function track(Order $order): JsonResponse
    {
        try {
            if ($order->user_id !== auth()->id()) {
                return $this->error('You are not authorized to track this order.', Response::HTTP_FORBIDDEN);
            }

            $order->load(['items.product:id,title,slug,image', 'items.deliveries']);

            $timeline = [];

            $timeline[] = [
                'status'    => 'order_placed',
                'label'     => 'Order Placed',
                'completed' => true,
                'date'      => $order->created_at->toISOString(),
            ];

            $timeline[] = [
                'status'    => 'payment_confirmed',
                'label'     => 'Payment Confirmed',
                'completed' => $order->payment_status === 'paid',
                'date'      => $order->paid_at?->toISOString(),
            ];

            $timeline[] = [
                'status'    => 'processing',
                'label'     => 'Processing',
                'completed' => in_array($order->status, ['processing', 'completed']),
                'date'      => $order->payment_status === 'paid' ? $order->paid_at?->toISOString() : null,
            ];

            $timeline[] = [
                'status'    => 'delivered',
                'label'     => 'Delivered',
                'completed' => $order->status === 'completed',
                'date'      => $order->completed_at?->toISOString(),
            ];

            $items = $order->items->map(function ($item) {
                return [
                    'id'      => $item->id,
                    'product' => [
                        'title' => $item->product?->title,
                        'image' => $item->product?->image,
                    ],
                    'quantity'        => $item->quantity,
                    'delivery_status' => $item->delivery_status,
                    'deliveries'      => $item->deliveries->map(fn ($d) => [
                        'id'           => $d->id,
                        'method'       => $d->delivery_method,
                        'status'       => $d->status,
                        'delivered_at' => $d->delivered_at?->toISOString(),
                    ]),
                ];
            });

            return $this->success([
                'order_number'   => $order->order_number,
                'status'         => $order->status,
                'payment_status' => $order->payment_status,
                'timeline'       => $timeline,
                'items'          => $items,
            ]);
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Unable to fetch tracking information.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get order invoice
     *
     * Retrieve the invoice for a paid order including totals, status,
     * and a download URL for the PDF. Only available after payment is confirmed.
     *
     * @authenticated
     *
     * @urlParam order integer required The order ID. Example: 1
     *
     * @response 200 {"status":true,"message":"Success","data":{"invoice_number":"INV-000001","status":"paid","subtotal":29.99,"tax_total":2.40,"discount_total":5.00,"grand_total":27.39,"currency":"USD","issued_at":"2026-02-28T00:00:00.000Z","paid_at":"2026-02-28T00:00:00.000Z","download_url":"https://example.com/invoices/1/download"}}
     * @response 403 {"status":false,"message":"Unauthorized."}
     * @response 404 {"status":false,"message":"Invoice not available yet. Payment must be confirmed first."}
     */
    public function invoice(Order $order): JsonResponse
    {
        try {
            if ($order->user_id !== auth()->id()) {
                return $this->error('Unauthorized.', Response::HTTP_FORBIDDEN);
            }

            $order->load('invoice');

            if (!$order->invoice) {
                return $this->error('Invoice not available yet. Payment must be confirmed first.', Response::HTTP_NOT_FOUND);
            }

            $invoice = $order->invoice;

            return $this->success([
                'invoice_number' => $invoice->invoice_number,
                'status'         => $invoice->status,
                'subtotal'       => (float) $invoice->subtotal,
                'tax_total'      => (float) $invoice->tax_total,
                'discount_total' => (float) $invoice->discount_total,
                'grand_total'    => (float) $invoice->grand_total,
                'currency'       => $invoice->currency,
                'issued_at'      => $invoice->issued_at?->toISOString(),
                'paid_at'        => $invoice->paid_at?->toISOString(),
                'download_url'   => route('invoices.download', $invoice->id),
            ]);
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Unable to fetch invoice.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Reorder
     *
     * Pre-fill checkout data from a previous order. Returns available items
     * with current stock levels, flags unavailable items, and includes
     * the saved billing address. Use the returned data to submit a new order.
     *
     * @authenticated
     *
     * @urlParam order integer required The original order ID. Example: 1
     *
     * @response 200 {"status":true,"message":"All items are available for reorder.","data":{"items":[{"seller_offer_id":5,"quantity":2,"available_stock":10,"product":{"id":1,"title":"Windows 11 Pro","price":29.99}}],"unavailable":[],"billing":{"name":"John Doe","email":"john@example.com"},"currency":"USD","payment_method":"stripe"}}
     * @response 403 {"status":false,"message":"Unauthorized."}
     */
    public function reorder(Order $order): JsonResponse
    {
        try {
            if ($order->user_id !== auth()->id()) {
                return $this->error('Unauthorized.', Response::HTTP_FORBIDDEN);
            }

            $order->load('items.offer', 'billingAddress');

            $items = [];
            $unavailable = [];

            foreach ($order->items as $item) {
                if (!$item->offer || $item->offer->status !== 'active') {
                    $unavailable[] = $item->product?->title ?? "Product #{$item->product_id}";
                    continue;
                }

                $stock = $item->offer->keys()->where('status', 'available')->count();

                $items[] = [
                    'seller_offer_id' => $item->seller_offer_id,
                    'quantity'        => min($item->quantity, $stock),
                    'available_stock' => $stock,
                    'product'         => [
                        'id'    => $item->product_id,
                        'title' => $item->product?->title,
                        'price' => (float) $item->offer->retail_price,
                    ],
                ];
            }

            $billing = $order->billingAddress ? $order->billingAddress->only([
                'name', 'email', 'phone', 'address', 'city', 'state', 'country', 'postal_code',
            ]) : null;

            $message = count($unavailable) > 0
                ? count($unavailable) . ' item(s) are no longer available.'
                : 'All items are available for reorder.';

            return $this->success([
                'items'           => $items,
                'unavailable'     => $unavailable,
                'billing'         => $billing,
                'currency'        => $order->currency,
                'payment_method'  => $order->payment_method,
            ], $message);
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Unable to prepare reorder.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * List seller orders
     *
     * Get paginated orders that contain the authenticated seller's items.
     * Only items belonging to the seller are included in each order.
     * Requires the authenticated user to have an active seller account.
     *
     * @group Seller Orders
     * @authenticated
     *
     * @queryParam status string Filter by order status (pending, processing, completed, cancelled). Example: processing
     * @queryParam payment_status string Filter by payment status (pending, paid, failed, refunded). Example: paid
     * @queryParam per_page integer Results per page (default 15, max 50). Example: 10
     *
     * @response 200 {"status":true,"message":"Success","data":{"current_page":1,"data":[],"total":0}}
     * @response 404 {"status":false,"message":"Seller account not found."}
     */
    public function sellerOrders(Request $request): JsonResponse
    {
        try {
            $seller = Seller::where('user_id', auth()->id())->first();

            if (!$seller) {
                return $this->error('Seller account not found.', Response::HTTP_NOT_FOUND);
            }

            $perPage = min((int) $request->input('per_page', 15), 50);

            $orders = Order::whereHas('items', fn ($q) => $q->where('seller_id', $seller->id))
                ->with(['items' => fn ($q) => $q->where('seller_id', $seller->id)->with('product:id,title,slug,image')])
                ->with('user:id,name,email')
                ->when($request->status, fn ($q) => $q->where('status', $request->status))
                ->when($request->payment_status, fn ($q) => $q->where('payment_status', $request->payment_status))
                ->latest()
                ->paginate($perPage);

            return $this->success($orders);
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Unable to fetch seller orders.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Show seller order details
     *
     * Get detailed view of an order scoped to the seller's items only.
     * Includes per-item earnings breakdown (gross, commission, net) and
     * delivery status. The order must contain at least one of the seller's products.
     *
     * @group Seller Orders
     * @authenticated
     *
     * @urlParam order integer required The order ID. Example: 1
     *
     * @response 200 {"status":true,"message":"Success","data":{"order":{"id":1,"order_number":"000001","status":"processing"},"customer":{"name":"John Doe","email":"john@example.com"},"items":[],"earnings_summary":{"total_gross":29.99,"total_commission":3.00,"total_net":26.99}}}
     * @response 403 {"status":false,"message":"This order does not contain your products."}
     * @response 404 {"status":false,"message":"Seller account not found."}
     */
    public function sellerOrderShow(Order $order): JsonResponse
    {
        try {
            $seller = Seller::where('user_id', auth()->id())->first();

            if (!$seller) {
                return $this->error('Seller account not found.', Response::HTTP_NOT_FOUND);
            }

            $hasItems = $order->items()->where('seller_id', $seller->id)->exists();
            if (!$hasItems) {
                return $this->error('This order does not contain your products.', Response::HTTP_FORBIDDEN);
            }

            $order->load([
                'items' => fn ($q) => $q->where('seller_id', $seller->id)
                    ->with(['product:id,title,slug,image', 'deliveries', 'earning']),
                'user:id,name,email',
                'billingAddress',
            ]);

            $sellerEarnings = $order->items->sum(fn ($i) => (float) ($i->earning?->net_amount ?? 0));
            $sellerCommission = $order->items->sum(fn ($i) => (float) ($i->earning?->commission ?? 0));

            return $this->success([
                'order' => [
                    'id'             => $order->id,
                    'order_number'   => $order->order_number,
                    'status'         => $order->status,
                    'payment_status' => $order->payment_status,
                    'currency'       => $order->currency,
                    'created_at'     => $order->created_at?->toISOString(),
                ],
                'customer' => [
                    'name'  => $order->user?->name,
                    'email' => $order->user?->email,
                ],
                'billing' => $order->billingAddress?->only([
                    'name', 'country',
                ]),
                'items' => $order->items->map(function ($item) {
                    return [
                        'id'              => $item->id,
                        'product'         => $item->product?->only(['id', 'title', 'slug', 'image']),
                        'quantity'        => $item->quantity,
                        'unit_price'      => (float) $item->unit_price,
                        'subtotal'        => (float) $item->subtotal,
                        'delivery_status' => $item->delivery_status,
                        'status'          => $item->status,
                        'earning'         => $item->earning ? [
                            'gross'      => (float) $item->earning->gross_amount,
                            'commission' => (float) $item->earning->commission,
                            'net'        => (float) $item->earning->net_amount,
                            'status'     => $item->earning->status,
                        ] : null,
                        'deliveries' => $item->deliveries->map(fn ($d) => [
                            'id'           => $d->id,
                            'method'       => $d->delivery_method,
                            'status'       => $d->status,
                            'delivered_at' => $d->delivered_at?->toISOString(),
                        ]),
                    ];
                }),
                'earnings_summary' => [
                    'total_gross'      => $sellerEarnings + $sellerCommission,
                    'total_commission' => $sellerCommission,
                    'total_net'        => $sellerEarnings,
                ],
            ]);
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Unable to fetch seller order details.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Seller earnings
     *
     * Get paginated earnings history and a financial summary for the
     * authenticated seller. Summary includes totals for gross revenue,
     * platform commission, net earnings, and balances by status
     * (pending, available, paid).
     *
     * @group Seller Orders
     * @authenticated
     *
     * @queryParam status string Filter by earning status (pending, available, paid). Example: available
     * @queryParam per_page integer Results per page (default 20, max 50). Example: 10
     *
     * @response 200 {"status":true,"message":"Success","data":{"earnings":{"current_page":1,"data":[],"total":0},"summary":{"total_gross":299.90,"total_commission":30.00,"total_net":269.90,"pending":100.00,"available":150.00,"paid":19.90}}}
     * @response 404 {"status":false,"message":"Seller account not found."}
     */
    public function sellerEarnings(Request $request): JsonResponse
    {
        try {
            $seller = Seller::where('user_id', auth()->id())->first();

            if (!$seller) {
                return $this->error('Seller account not found.', Response::HTTP_NOT_FOUND);
            }

            $perPage = min((int) $request->input('per_page', 20), 50);

            $earnings = SellerEarning::where('seller_id', $seller->id)
                ->with(['order:id,order_number,created_at,status', 'orderItem.product:id,title'])
                ->when($request->status, fn ($q) => $q->where('status', $request->status))
                ->latest()
                ->paginate($perPage);

            $totals = SellerEarning::where('seller_id', $seller->id)
                ->selectRaw("
                    SUM(gross_amount) as total_gross,
                    SUM(commission) as total_commission,
                    SUM(net_amount) as total_net,
                    SUM(CASE WHEN status = 'pending' THEN net_amount ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'available' THEN net_amount ELSE 0 END) as available,
                    SUM(CASE WHEN status = 'paid' THEN net_amount ELSE 0 END) as paid
                ")
                ->first();

            return $this->success([
                'earnings' => $earnings,
                'summary'  => [
                    'total_gross'      => (float) ($totals->total_gross ?? 0),
                    'total_commission' => (float) ($totals->total_commission ?? 0),
                    'total_net'        => (float) ($totals->total_net ?? 0),
                    'pending'          => (float) ($totals->pending ?? 0),
                    'available'        => (float) ($totals->available ?? 0),
                    'paid'             => (float) ($totals->paid ?? 0),
                ],
            ]);
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Unable to fetch seller earnings.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
