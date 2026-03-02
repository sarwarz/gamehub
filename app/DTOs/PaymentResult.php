<?php

namespace App\DTOs;

class PaymentResult
{
    public function __construct(
        public readonly string  $gatewayReference,
        public readonly ?string $clientSecret = null,
        public readonly ?string $approvalUrl = null,
        public readonly ?string $paymentUrl = null,
        public readonly array   $raw = [],
    ) {}

    public function toFrontend(): array
    {
        return array_filter([
            'gateway_reference' => $this->gatewayReference,
            'client_secret'     => $this->clientSecret,
            'approval_url'      => $this->approvalUrl,
            'payment_url'       => $this->paymentUrl,
        ]);
    }
}
