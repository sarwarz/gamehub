<?php

namespace App\PaymentGateways;

use App\Contracts\PaymentGatewayInterface;
use App\DTOs\PaymentResult;
use App\DTOs\RefundResult;
use App\DTOs\WebhookPayload;
use App\Models\CheckoutSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeGateway implements PaymentGatewayInterface
{
    public function createPayment(CheckoutSession $session, array $config): PaymentResult
    {
        \Stripe\Stripe::setApiKey($config['secret_key']);

        $metadata = ['trx' => $session->trx];

        if ($session->meta['wallet_transaction_id'] ?? null) {
            $metadata['wallet_transaction_id'] = $session->meta['wallet_transaction_id'];
        }

        $intent = \Stripe\PaymentIntent::create([
            'amount'   => (int) round($session->gateway_amount * 100),
            'currency' => strtolower($session->currency),
            'metadata' => $metadata,
            'automatic_payment_methods' => ['enabled' => true],
        ]);

        return new PaymentResult(
            gatewayReference: $intent->id,
            clientSecret: $intent->client_secret,
        );
    }

    public function verifyWebhook(Request $request, array $config, string $mode): WebhookPayload
    {
        if ($mode === 'sandbox') {
            return $this->parseSandbox($request);
        }

        $secret = $config['webhook_secret'] ?? null;

        if (!$secret) {
            Log::error('Stripe webhook_secret not configured');
            return new WebhookPayload(success: false);
        }

        try {
            $event = \Stripe\Webhook::constructEvent(
                $request->getContent(),
                $request->header('Stripe-Signature'),
                $secret
            );
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::warning('Stripe signature verification failed', ['error' => $e->getMessage()]);
            return new WebhookPayload(success: false);
        } catch (\UnexpectedValueException $e) {
            Log::warning('Stripe invalid payload', ['error' => $e->getMessage()]);
            return new WebhookPayload(success: false);
        }

        if ($event->type !== 'payment_intent.succeeded') {
            return new WebhookPayload(success: false);
        }

        $intent   = $event->data->object;
        $metadata = $intent->metadata;

        return new WebhookPayload(
            success: true,
            eventId: $event->id,
            eventType: $event->type,
            trx: $metadata->trx ?? null,
            walletTransactionId: isset($metadata->wallet_transaction_id) ? (int) $metadata->wallet_transaction_id : null,
            gatewayReference: $intent->id,
            amount: $intent->amount_received / 100,
            currency: strtoupper($intent->currency),
            raw: [
                'stripe_event_id'   => $event->id,
                'payment_intent_id' => $intent->id,
                'amount_received'   => $intent->amount_received,
                'currency'          => $intent->currency,
            ],
        );
    }

    public function refund(string $gatewayReference, float $amount, string $currency, array $config): RefundResult
    {
        try {
            \Stripe\Stripe::setApiKey($config['secret_key']);

            $refund = \Stripe\Refund::create([
                'payment_intent' => $gatewayReference,
                'amount'         => (int) round($amount * 100),
            ]);

            return new RefundResult(
                success: $refund->status === 'succeeded',
                refundId: $refund->id,
                raw: ['status' => $refund->status],
            );
        } catch (\Throwable $e) {
            Log::error('Stripe refund failed', ['error' => $e->getMessage()]);
            return new RefundResult(success: false, error: $e->getMessage());
        }
    }

    protected function parseSandbox(Request $request): WebhookPayload
    {
        $data     = $request->input('data.object', []);
        $metadata = $data['metadata'] ?? [];

        $succeeded = $request->input('type') === 'payment_intent.succeeded';

        return new WebhookPayload(
            success: $succeeded,
            eventId: $request->input('id'),
            eventType: $request->input('type'),
            trx: $metadata['trx'] ?? null,
            walletTransactionId: isset($metadata['wallet_transaction_id']) ? (int) $metadata['wallet_transaction_id'] : null,
            gatewayReference: $data['id'] ?? null,
            amount: isset($data['amount_received']) ? $data['amount_received'] / 100 : null,
            currency: isset($data['currency']) ? strtoupper($data['currency']) : null,
            raw: $request->all(),
        );
    }
}
