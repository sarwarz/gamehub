<?php

namespace App\Contracts;

use App\DTOs\PaymentResult;
use App\DTOs\RefundResult;
use App\DTOs\WebhookPayload;
use App\Models\CheckoutSession;
use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    /**
     * Create a payment on the gateway and return data the frontend needs
     * (client_secret for Stripe, approval_url for PayPal, payment_url for Cryptomus).
     */
    public function createPayment(CheckoutSession $session, array $config): PaymentResult;

    /**
     * Verify the webhook signature and extract a normalized payload.
     * Must return success=false if verification fails.
     */
    public function verifyWebhook(Request $request, array $config, string $mode): WebhookPayload;

    /**
     * Refund a previously captured payment.
     */
    public function refund(string $gatewayReference, float $amount, string $currency, array $config): RefundResult;
}
