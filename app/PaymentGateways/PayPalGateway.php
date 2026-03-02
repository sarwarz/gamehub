<?php

namespace App\PaymentGateways;

use App\Contracts\PaymentGatewayInterface;
use App\DTOs\PaymentResult;
use App\DTOs\RefundResult;
use App\DTOs\WebhookPayload;
use App\Models\CheckoutSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayPalGateway implements PaymentGatewayInterface
{
    public function createPayment(CheckoutSession $session, array $config): PaymentResult
    {
        $baseUrl     = $this->baseUrl($config['mode'] ?? 'sandbox');
        $accessToken = $this->getAccessToken($config, $baseUrl);

        $customId = json_encode(['trx' => $session->trx]);

        $response = Http::withToken($accessToken)
            ->timeout(15)
            ->post("{$baseUrl}/v2/checkout/orders", [
                'intent'         => 'CAPTURE',
                'purchase_units' => [
                    [
                        'custom_id'   => $customId,
                        'description' => "Order {$session->trx}",
                        'amount'      => [
                            'currency_code' => strtoupper($session->currency),
                            'value'         => number_format($session->gateway_amount, 2, '.', ''),
                        ],
                    ],
                ],
                'application_context' => [
                    'return_url' => config('app.frontend_url', config('app.url')) . '/checkout/success?session=' . $session->uuid,
                    'cancel_url' => config('app.frontend_url', config('app.url')) . '/checkout/cancel?session=' . $session->uuid,
                ],
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('PayPal order creation failed: ' . $response->body());
        }

        $data        = $response->json();
        $approvalUrl = collect($data['links'] ?? [])->firstWhere('rel', 'approve')['href'] ?? null;

        return new PaymentResult(
            gatewayReference: $data['id'],
            approvalUrl: $approvalUrl,
            raw: $data,
        );
    }

    public function verifyWebhook(Request $request, array $config, string $mode): WebhookPayload
    {
        if ($mode === 'sandbox') {
            return $this->extractPayload($request->all());
        }

        $webhookId = $config['webhook_id'] ?? null;

        if (!$webhookId) {
            Log::error('PayPal webhook_id not configured');
            return new WebhookPayload(success: false);
        }

        if (!$this->verifySignature($config, $request, $webhookId, $mode)) {
            return new WebhookPayload(success: false);
        }

        return $this->extractPayload($request->all());
    }

    public function refund(string $gatewayReference, float $amount, string $currency, array $config): RefundResult
    {
        try {
            $baseUrl     = $this->baseUrl($config['mode'] ?? 'sandbox');
            $accessToken = $this->getAccessToken($config, $baseUrl);

            $response = Http::withToken($accessToken)
                ->timeout(15)
                ->post("{$baseUrl}/v2/payments/captures/{$gatewayReference}/refund", [
                    'amount' => [
                        'value'         => number_format($amount, 2, '.', ''),
                        'currency_code' => strtoupper($currency),
                    ],
                ]);

            if (!$response->successful()) {
                return new RefundResult(success: false, error: $response->body());
            }

            $data = $response->json();

            return new RefundResult(
                success: ($data['status'] ?? '') === 'COMPLETED',
                refundId: $data['id'] ?? null,
                raw: $data,
            );
        } catch (\Throwable $e) {
            Log::error('PayPal refund failed', ['error' => $e->getMessage()]);
            return new RefundResult(success: false, error: $e->getMessage());
        }
    }

    protected function extractPayload(array $event): WebhookPayload
    {
        $eventType = $event['event_type'] ?? '';
        $resource  = $event['resource'] ?? [];

        $accepted = ['PAYMENT.CAPTURE.COMPLETED', 'CHECKOUT.ORDER.APPROVED'];

        if (!in_array($eventType, $accepted)) {
            return new WebhookPayload(success: false);
        }

        $customId   = null;
        $gatewayRef = null;
        $amount     = null;
        $currency   = null;

        if ($eventType === 'PAYMENT.CAPTURE.COMPLETED') {
            $customId   = $resource['custom_id'] ?? null;
            $gatewayRef = $resource['id'] ?? null;
            $amount     = isset($resource['amount']['value']) ? (float) $resource['amount']['value'] : null;
            $currency   = $resource['amount']['currency_code'] ?? null;
        } else {
            $unit       = data_get($resource, 'purchase_units.0', []);
            $customId   = $unit['custom_id'] ?? null;
            $gatewayRef = $resource['id'] ?? null;
            $amount     = isset($unit['amount']['value']) ? (float) $unit['amount']['value'] : null;
            $currency   = $unit['amount']['currency_code'] ?? null;
        }

        $parsed = $this->parseCustomId($customId);

        return new WebhookPayload(
            success: !empty($parsed['trx']),
            eventId: $event['id'] ?? null,
            eventType: $eventType,
            trx: $parsed['trx'],
            walletTransactionId: $parsed['wallet_transaction_id'],
            gatewayReference: $gatewayRef,
            amount: $amount,
            currency: $currency,
            raw: [
                'paypal_event_id' => $event['id'] ?? null,
                'event_type'      => $eventType,
                'payer_email'     => data_get($event, 'resource.payer.email_address'),
            ],
        );
    }

    protected function verifySignature(array $config, Request $request, string $webhookId, string $mode): bool
    {
        $clientId = $config['client_id'] ?? '';
        $secret   = $config['secret_key'] ?? '';

        if (!$clientId || !$secret) {
            Log::error('PayPal client_id or secret_key missing');
            return false;
        }

        $baseUrl = $this->baseUrl($mode);

        try {
            $accessToken = $this->getAccessToken($config, $baseUrl);

            $response = Http::withToken($accessToken)
                ->timeout(15)
                ->post("{$baseUrl}/v1/notifications/verify-webhook-signature", [
                    'auth_algo'         => $request->header('PAYPAL-AUTH-ALGO'),
                    'cert_url'          => $request->header('PAYPAL-CERT-URL'),
                    'transmission_id'   => $request->header('PAYPAL-TRANSMISSION-ID'),
                    'transmission_sig'  => $request->header('PAYPAL-TRANSMISSION-SIG'),
                    'transmission_time' => $request->header('PAYPAL-TRANSMISSION-TIME'),
                    'webhook_id'        => $webhookId,
                    'webhook_event'     => $request->all(),
                ]);

            if (!$response->successful()) {
                Log::error('PayPal webhook verification failed', ['status' => $response->status()]);
                return false;
            }

            return $response->json('verification_status') === 'SUCCESS';
        } catch (\Throwable $e) {
            Log::error('PayPal signature error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    protected function getAccessToken(array $config, string $baseUrl): string
    {
        $response = Http::withBasicAuth($config['client_id'], $config['secret_key'])
            ->asForm()
            ->timeout(15)
            ->post("{$baseUrl}/v1/oauth2/token", ['grant_type' => 'client_credentials']);

        if (!$response->successful()) {
            throw new \RuntimeException('PayPal OAuth failed: ' . $response->body());
        }

        return $response->json('access_token');
    }

    protected function parseCustomId(?string $value): array
    {
        if (!$value) {
            return ['trx' => null, 'wallet_transaction_id' => null];
        }

        $decoded = json_decode($value, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return [
                'trx'                   => $decoded['trx'] ?? null,
                'wallet_transaction_id' => isset($decoded['wallet_transaction_id']) ? (int) $decoded['wallet_transaction_id'] : null,
            ];
        }

        return ['trx' => $value, 'wallet_transaction_id' => null];
    }

    protected function baseUrl(string $mode): string
    {
        return $mode === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }
}
