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

class CryptomusGateway implements PaymentGatewayInterface
{
    public function createPayment(CheckoutSession $session, array $config): PaymentResult
    {
        $merchantId = $config['merchant_id'];
        $apiKey     = $config['api_key'];

        $body = [
            'amount'   => number_format($session->gateway_amount, 2, '.', ''),
            'currency' => strtoupper($session->currency),
            'order_id' => $session->trx,
            'url_callback' => route('payment.webhook', ['gateway' => 'cryptomus']),
            'url_return'   => config('app.frontend_url', config('app.url')) . '/checkout/success?session=' . $session->uuid,
        ];

        $sign = md5(
            base64_encode(json_encode($body, JSON_UNESCAPED_UNICODE)) . $apiKey
        );

        $response = Http::withHeaders([
                'merchant' => $merchantId,
                'sign'     => $sign,
            ])
            ->timeout(15)
            ->post('https://api.cryptomus.com/v1/payment', $body);

        if (!$response->successful()) {
            throw new \RuntimeException('Cryptomus payment creation failed: ' . $response->body());
        }

        $data = $response->json('result', []);

        return new PaymentResult(
            gatewayReference: $data['uuid'] ?? '',
            paymentUrl: $data['url'] ?? null,
            raw: $data,
        );
    }

    public function verifyWebhook(Request $request, array $config, string $mode): WebhookPayload
    {
        $apiKey = $config['api_key'] ?? null;

        if (!$apiKey) {
            Log::error('Cryptomus api_key not configured');
            return new WebhookPayload(success: false);
        }

        $data         = $request->all();
        $receivedSign = $data['sign'] ?? null;

        if (!$receivedSign) {
            Log::warning('Cryptomus webhook missing sign');
            return new WebhookPayload(success: false);
        }

        $payload = $data;
        unset($payload['sign']);
        ksort($payload);

        $expectedSign = md5(
            base64_encode(json_encode($payload, JSON_UNESCAPED_UNICODE)) . $apiKey
        );

        if (!hash_equals($expectedSign, $receivedSign)) {
            Log::warning('Cryptomus signature mismatch', ['ip' => $request->ip()]);
            return new WebhookPayload(success: false);
        }

        $status          = $data['status'] ?? '';
        $successStatuses = ['paid', 'paid_over'];

        if (!in_array($status, $successStatuses)) {
            return new WebhookPayload(success: false);
        }

        $orderId = $data['order_id'] ?? null;
        $parsed  = $this->parseOrderId($orderId);

        return new WebhookPayload(
            success: true,
            eventId: $data['uuid'] ?? null,
            eventType: $status,
            trx: $parsed['trx'],
            walletTransactionId: $parsed['wallet_transaction_id'],
            gatewayReference: $data['uuid'] ?? null,
            amount: isset($data['payment_amount']) ? (float) $data['payment_amount'] : (isset($data['amount']) ? (float) $data['amount'] : null),
            currency: $data['currency'] ?? null,
            raw: [
                'cryptomus_uuid' => $data['uuid'] ?? null,
                'status'         => $status,
                'amount'         => $data['amount'] ?? null,
                'payment_amount' => $data['payment_amount'] ?? null,
                'currency'       => $data['currency'] ?? null,
                'network'        => $data['network'] ?? null,
                'txid'           => $data['txid'] ?? null,
            ],
        );
    }

    public function refund(string $gatewayReference, float $amount, string $currency, array $config): RefundResult
    {
        return new RefundResult(
            success: false,
            error: 'Cryptomus does not support automated refunds. Process manually.',
        );
    }

    protected function parseOrderId(?string $value): array
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
}
