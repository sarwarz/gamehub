<?php

namespace App\DTOs;

class WebhookPayload
{
    public function __construct(
        public readonly bool    $success,
        public readonly ?string $eventId = null,
        public readonly ?string $eventType = null,
        public readonly ?string $trx = null,
        public readonly ?int    $walletTransactionId = null,
        public readonly ?string $gatewayReference = null,
        public readonly ?float  $amount = null,
        public readonly ?string $currency = null,
        public readonly array   $raw = [],
    ) {}

    public function isWalletDeposit(): bool
    {
        return $this->walletTransactionId !== null;
    }
}
