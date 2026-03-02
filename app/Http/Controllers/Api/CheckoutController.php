<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\CheckoutService;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Checkout
 *
 * Payment-first checkout flow for purchasing digital products.
 * The cart lives entirely on the client; these endpoints validate items,
 * calculate totals, process payment, and create the order.
 *
 * ## How Checkout Works
 *
 * GameHub uses a **payment-first** model — the order is only created after
 * the payment gateway confirms the money was received. This eliminates
 * unpaid orders and stock-holding issues.
 *
 * ## Integration Steps
 *
 * ### Step 1: Create a Checkout Session
 *
 * ```
 * POST /api/v1/checkout/sessions
 * ```
 *
 * Send your cart items (offer IDs + quantities), billing address, currency,
 * and optional coupon code. The server validates stock, calculates taxes
 * and discounts, and returns a **session UUID** with a 30-minute TTL.
 *
 * **Idempotency:** Pass an `X-Idempotency-Key` header to prevent duplicate
 * sessions if the request is retried.
 *
 * ### Step 2: Initiate Payment
 *
 * ```
 * POST /api/v1/checkout/sessions/{uuid}/pay
 * ```
 *
 * Choose a payment method and the server will:
 * - Re-validate stock availability
 * - Reserve the license keys
 * - Deduct wallet balance (if `use_wallet: true` or `payment_method: "wallet"`)
 * - Create a payment with the gateway
 *
 * **Response varies by gateway:**
 *
 * | Gateway    | Response field    | What to do                               |
 * |------------|-------------------|------------------------------------------|
 * | `stripe`   | `payment.client_secret`  | Use Stripe.js `confirmPayment()`  |
 * | `paypal`   | `payment.approval_url`   | Redirect user to PayPal           |
 * | `cryptomus`| `payment.payment_url`    | Redirect user to Cryptomus        |
 * | `wallet`   | (none — instant)         | Order is created immediately      |
 *
 * ### Step 3: User Completes Payment
 *
 * For **Stripe**: use `stripe.confirmPayment()` with the `client_secret`.
 * For **PayPal / Cryptomus**: redirect the user to the approval URL.
 * For **wallet**: skip this step — the order was already created.
 *
 * After the user pays, the payment gateway sends a webhook to
 * `POST /api/v1/webhooks/payment/{gateway}` which triggers order creation
 * in the background.
 *
 * ### Step 4: Poll for Result
 *
 * ```
 * GET /api/v1/checkout/sessions/{uuid}/result
 * ```
 *
 * Poll this endpoint every 2-3 seconds after the user returns from the
 * payment gateway. It returns `status: "paying"` while the webhook is
 * being processed, and `status: "completed"` with the full order object
 * once the order is created.
 *
 * ## Next.js Example
 *
 * ```javascript
 * // 1. Create session
 * const session = await api.post('/checkout/sessions', {
 *   items: [{ seller_offer_id: 5, quantity: 1 }],
 *   billing: { name: 'John', email: 'john@example.com', ... },
 *   currency: 'USD',
 * });
 *
 * // 2. Initiate payment
 * const payment = await api.post(`/checkout/sessions/${session.session_uuid}/pay`, {
 *   payment_method: 'stripe',
 * });
 *
 * // 3. Confirm with Stripe.js
 * await stripe.confirmPayment({ clientSecret: payment.payment.client_secret });
 *
 * // 4. Poll for result
 * let result;
 * do {
 *   await new Promise(r => setTimeout(r, 2000));
 *   result = await api.get(`/checkout/sessions/${session.session_uuid}/result`);
 * } while (result.status !== 'completed');
 *
 * // result.order.id, result.order.order_number, etc.
 * ```
 *
 * ## Error Handling
 *
 * | Error | Cause | What to do |
 * |-------|-------|------------|
 * | `422` "Insufficient stock" | Stock sold out between session creation and payment | Show stock error, re-create session |
 * | `422` "Checkout session has expired" | Session TTL (30 min) exceeded | Start a new checkout |
 * | `422` "Insufficient wallet balance" | Wallet pay attempted with low balance | Show balance, offer gateway payment |
 * | `422` "Invalid or expired coupon" | Coupon used up or expired | Remove coupon and retry |
 *
 * ## Session Statuses
 *
 * | Status      | Meaning |
 * |-------------|---------|
 * | `open`      | Session created, awaiting payment initiation |
 * | `paying`    | Payment initiated, waiting for gateway confirmation |
 * | `completed` | Payment confirmed, order created |
 * | `expired`   | Session TTL exceeded (keys released, wallet hold reverted) |
 */
class CheckoutController extends Controller
{
    public function __construct(
        protected CheckoutService $checkoutService,
    ) {}

    /**
     * Create checkout session
     *
     * Validate cart items, calculate taxes and discounts, and create a
     * temporary checkout session (30 min TTL). No order or payment is
     * created at this stage.
     *
     * @authenticated
     *
     * @bodyParam items array required Cart items.
     * @bodyParam items[].seller_offer_id integer required Seller offer ID. Example: 5
     * @bodyParam items[].quantity integer required Quantity. Example: 2
     * @bodyParam billing object required Billing address.
     * @bodyParam billing.name string required Full name. Example: John Doe
     * @bodyParam billing.email string required Email. Example: john@example.com
     * @bodyParam billing.phone string optional Phone. Example: +1234567890
     * @bodyParam billing.address string required Street. Example: 123 Main St
     * @bodyParam billing.city string required City. Example: New York
     * @bodyParam billing.state string optional State. Example: NY
     * @bodyParam billing.country string required Country code. Example: US
     * @bodyParam billing.postcode string optional Postal code. Example: 10001
     * @bodyParam currency string required Currency code. Example: USD
     * @bodyParam coupon_code string optional Coupon code. Example: SAVE20
     */
    public function createSession(Request $request): JsonResponse
    {
        $requireBilling = (bool) \App\Models\Setting::get('checkout', 'require_billing_address', true);
        $billingRule = $requireBilling ? 'required' : 'nullable';

        $request->validate([
            'items'                   => 'required|array|min:1',
            'items.*.seller_offer_id' => 'required|integer|distinct|exists:seller_offers,id',
            'items.*.quantity'        => 'required|integer|min:1|max:100',
            'billing.name'            => "{$billingRule}|string|max:255",
            'billing.email'           => "{$billingRule}|email|max:255",
            'billing.phone'           => 'nullable|string|max:30',
            'billing.address'         => "{$billingRule}|string|max:500",
            'billing.city'            => "{$billingRule}|string|max:100",
            'billing.state'           => 'nullable|string|max:100',
            'billing.country'         => "{$billingRule}|string|max:10",
            'billing.postcode'        => 'nullable|string|max:20',
            'currency'                => 'required|string|max:10',
            'coupon_code'             => 'nullable|string|max:50',
        ]);

        try {
            $session = $this->checkoutService->createSession(
                user: $request->user(),
                items: $request->items,
                billing: $request->billing,
                currency: $request->currency,
                couponCode: $request->coupon_code,
                idempotencyKey: $request->header('X-Idempotency-Key'),
            );

            return $this->success([
                'session_uuid'    => $session->uuid,
                'currency'        => $session->currency,
                'subtotal'        => (float) $session->subtotal,
                'tax_amount'      => (float) $session->tax_amount,
                'discount_amount' => (float) $session->discount_amount,
                'total_amount'    => (float) $session->total_amount,
                'base_currency'   => $session->base_currency,
                'base_total_amount' => (float) $session->base_total_amount,
                'exchange_rate'   => (float) $session->exchange_rate,
                'tax_details'     => $session->tax_data,
                'coupon'          => $session->coupon_data,
                'items'           => $session->items,
                'expires_at'      => $session->expires_at->toISOString(),
            ], 'Checkout session created.', Response::HTTP_CREATED);

        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Unable to create checkout session.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Pay for checkout session
     *
     * Select a payment method and initiate payment. Returns gateway-specific
     * data the frontend needs to complete the payment (client_secret for
     * Stripe, approval_url for PayPal, payment_url for Cryptomus).
     *
     * @authenticated
     *
     * @urlParam uuid string required Session UUID.
     * @bodyParam payment_method string required Payment method code. Example: stripe
     * @bodyParam use_wallet boolean optional Use wallet for partial payment. Example: false
     */
    public function pay(Request $request, string $uuid): JsonResponse
    {
        $request->validate([
            'payment_method' => 'required|string|in:wallet,stripe,paypal,cryptomus,tazapay,1d3,cod',
            'use_wallet'     => 'sometimes|boolean',
        ]);

        try {
            $session = \App\Models\CheckoutSession::where('uuid', $uuid)
                ->where('user_id', $request->user()->id)
                ->firstOrFail();

            $result = $this->checkoutService->initiatePayment(
                session: $session,
                paymentMethod: $request->payment_method,
                useWallet: $request->boolean('use_wallet'),
            );

            return $this->success($result, 'Payment initiated.');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->error('Checkout session not found.', Response::HTTP_NOT_FOUND);
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Unable to initiate payment.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get checkout result
     *
     * Check the status of a checkout session. Returns the created order
     * if payment is complete, or the current session status if still processing.
     *
     * @authenticated
     *
     * @urlParam uuid string required Session UUID.
     */
    public function result(Request $request, string $uuid): JsonResponse
    {
        try {
            $result = $this->checkoutService->getResult($uuid, $request->user()->id);

            return $this->success($result);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->error('Checkout session not found.', Response::HTTP_NOT_FOUND);
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Unable to fetch checkout result.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
