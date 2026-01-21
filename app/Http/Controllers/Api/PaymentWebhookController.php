<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Models\SellerEarning;
use App\Models\OrderNote;
use App\Services\InvoiceService;
use App\Jobs\AutoDeliverOrderJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;

class PaymentWebhookController extends Controller
{
    public function handle(string $gateway, Request $request)
    {
        $method = PaymentMethod::where('code', $gateway)
            ->where('is_enabled', true)
            ->first();

        if (!$method) {
            return response()->json(['message' => 'Payment method disabled'], 404);
        }

        try {
            // STEP 1: Normalize webhook
            $payload = $this->normalize($method, $request);

            if (!$payload['success'] || empty($payload['trx'])) {
                return response()->json(['message' => 'Invalid webhook'], 400);
            }

            $order = null;
            $transaction = null;

            DB::transaction(function () use (
                $payload,
                $gateway,
                &$order,
                &$transaction
            ) {

                // STEP 2: Lock transaction
                $transaction = Transaction::where('trx', $payload['trx'])
                    ->lockForUpdate()
                    ->first();

                if (!$transaction) {
                    throw new \Exception('Transaction not found');
                }

                // Idempotency
                if ($transaction->status === 'completed') {
                    return;
                }

                // STEP 3: Update transaction
                $transaction->update([
                    'status'  => 'completed',
                    'gateway' => $gateway,
                    'meta'    => array_merge($transaction->meta ?? [], $payload['raw']),
                ]);

                // STEP 4: Lock order
                $order = Order::lockForUpdate()->find($transaction->reference_id);

                if (!$order) {
                    throw new \Exception('Order not found');
                }

                // STEP 5: Update order
                $order->update([
                    'payment_status' => 'paid',
                    'status'         => 'processing',
                    'paid_at'        => now(),
                ]);

                // STEP 6: Activate seller earnings
                SellerEarning::where('order_id', $order->id)
                    ->update(['status' => 'available']);

                // STEP 7: Generate invoice
                app(InvoiceService::class)->generateFromOrder($order);
            });

            /**
             * 🔥 SAFE SECTION (NO ROLLBACK POSSIBLE)
             * Runs ONLY after successful commit
             */
            DB::afterCommit(function () use ($order, $transaction, $gateway) {

                // Payment success
                $order->notes()->create([
                    'note' => "Payment completed successfully via {$gateway}. Transaction ID: {$transaction->trx}",
                    'type' => 'system',
                    'is_visible_to_customer' => true,
                ]);

                // Order processing
                $order->notes()->create([
                    'note' => 'Order marked as processing after successful payment.',
                    'type' => 'system',
                ]);

                // Seller earnings
                $order->notes()->create([
                    'note' => 'Seller earnings activated and marked as available.',
                    'type' => 'system',
                ]);

                // Invoice
                $order->notes()->create([
                    'note' => 'Invoice generated for the order.',
                    'type' => 'system',
                    'is_visible_to_customer' => true,
                ]);

                // Dispatch delivery AFTER commit
                dispatch(new AutoDeliverOrderJob($order->id));

                $order->notes()->create([
                    'note' => 'Auto delivery job dispatched.',
                    'type' => 'system',
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
            ], Response::HTTP_OK);

        } catch (\Throwable $e) {

            Log::error('Payment webhook failed', [
                'gateway' => $gateway,
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Webhook error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Normalize & verify webhook payload
     */
    protected function normalize(PaymentMethod $method, Request $request): array
    {
        return match ($method->code) {
            'paypal' => $this->paypal($method, $request),
            'stripe' => $this->stripe($method, $request),
            default  => ['success' => false],
        };
    }

    protected function paypal(PaymentMethod $method, Request $request): array
    {
        if (app()->environment('local', 'testing')) {
            return [
                'success' => $request->input('type') === 'payment_intent.succeeded',
                'trx'     => data_get($request->input('data'), 'object.metadata.trx'),
                'raw'     => $request->all(),
            ];
        }

        return ['success' => false];
    }

    protected function stripe(PaymentMethod $method, Request $request): array
    {
        if (app()->environment('local', 'testing')) {
            return [
                'success' => $request->input('type') === 'payment_intent.succeeded',
                'trx'     => data_get($request->input('data'), 'object.metadata.trx'),
                'raw'     => $request->all(),
            ];
        }

        $secret = $method->config['webhook_secret'] ?? null;
        if (!$secret) return ['success' => false];

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
