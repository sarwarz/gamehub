<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Models\SellerEarning;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;

class PaymentWebhookController extends Controller
{
    /**
     * Handle Payment Webhook
     *
     * Receive and process payment gateway webhooks.
     * This endpoint validates the webhook payload, updates the transaction,
     * marks the order as paid, activates seller earnings, and generates an invoice.
     *
     * ⚠️ This endpoint is intended to be called **only by payment gateways**.
     *
     * @group Payments
     *
     * @urlParam gateway string required Payment gateway code. Example: stripe
     *
     * @bodyParam type string Webhook event type (Stripe). Example: payment_intent.succeeded
     * @bodyParam data object Webhook payload data.
     * @bodyParam data.object object Payment object.
     * @bodyParam data.object.metadata.trx string Transaction reference. Example: trx_65af8d2c1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Payment processed successfully"
     * }
     *
     * @response 200 {
     *   "message": "Already processed"
     * }
     *
     * @response 400 {
     *   "message": "Invalid webhook"
     * }
     *
     * @response 404 {
     *   "message": "Payment method disabled"
     * }
     *
     * @response 404 {
     *   "success": false,
     *   "message": "Transaction not found"
     * }
     *
     * @response 500 {
     *   "success": false,
     *   "message": "Webhook error"
     * }
     */
    public function handle(string $gateway, Request $request)
    {
        $method = PaymentMethod::where('code', $gateway)
            ->where('is_enabled', true)
            ->first();

        if (!$method) {
            return response()->json(['message' => 'Payment method disabled'], 404);
        }

        try {
            // STEP 1: Normalize & verify payload
            $payload = $this->normalize($method, $request);

            if (!$payload['success']) {
                return response()->json(['message' => 'Invalid webhook'], 400);
            }

            return DB::transaction(function () use ($payload, $gateway) {

                // STEP 2: Lock transaction
                $transaction = Transaction::where('trx', $payload['trx'])
                    ->lockForUpdate()
                    ->first();

                if (!$transaction) {
                    Log::warning('Transaction not found', [
                        'trx' => $payload['trx'],
                        'gateway' => $gateway,
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Transaction not found',
                    ], 404);
                }

                // Idempotency protection
                if ($transaction->status === 'completed') {
                    return response()->json([
                        'message' => 'Already processed'
                    ], 200);
                }

                // STEP 3: Update transaction
                $transaction->update([
                    'status'  => 'completed',
                    'gateway' => $gateway,
                    'meta'    => array_merge($transaction->meta ?? [], $payload['raw']),
                ]);

                // STEP 4: Update order
                $order = Order::lockForUpdate()->find($transaction->reference_id);

                if (!$order) {
                    Log::error('Order not found for transaction', [
                        'trx' => $transaction->trx,
                        'reference_id' => $transaction->reference_id,
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Order not found',
                    ], 404);
                }

                $order->update([
                    'payment_status' => 'paid',
                    'status'         => 'processing',
                    'paid_at'        => now(),
                ]);

                // STEP 5: Activate seller earnings
                SellerEarning::where('order_id', $order->id)
                    ->update(['status' => 'available']);

                // STEP 6: Generate invoice
                app(InvoiceService::class)->generateFromOrder($order);

                // STEP 7: Dispatch delivery job (optional)
                // dispatch(new \App\Jobs\DeliverOrderJob($order->id));

                return response()->json([
                    'success' => true,
                    'message' => 'Payment processed successfully',
                ], Response::HTTP_OK);
            });

        } catch (\Throwable $e) {

            Log::error('Payment webhook failed', [
                'gateway' => $gateway,
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Webhook error',
            ], 500);
        }
    }

    /**
     * Normalize & verify webhook payload
     *
     * @internal
     */
    protected function normalize(PaymentMethod $method, Request $request): array
    {
        return match ($method->code) {
            'stripe' => $this->stripe($method, $request),
            default  => ['success' => false],
        };
    }

    /**
     * Stripe webhook normalization
     *
     * @internal
     */
    protected function stripe(PaymentMethod $method, Request $request): array
    {
        /**
         * TEST MODE (Local / Postman)
         */
        if (app()->environment('local', 'testing')) {
            return [
                'success' => $request->input('type') === 'payment_intent.succeeded',
                'trx'     => data_get($request->input('data'), 'object.metadata.trx'),
                'raw'     => $request->all(),
            ];
        }

        /**
         * PRODUCTION MODE (Real Stripe Webhook)
         */
        $secret = $method->config['webhook_secret'] ?? null;

        if (!$secret) {
            return ['success' => false];
        }

        try {
            $event = \Stripe\Webhook::constructEvent(
                $request->getContent(),
                $request->header('Stripe-Signature'),
                $secret
            );

            if ($event->type !== 'payment_intent.succeeded') {
                return ['success' => false];
            }

            return [
                'success' => true,
                'trx'     => $event->data->object->metadata->trx ?? null,
                'raw'     => (array) $event,
            ];

        } catch (\Throwable) {
            return ['success' => false];
        }
    }
}
