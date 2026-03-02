<?php

namespace App\DTOs;

class RefundResult
{
    public function __construct(
        public readonly bool    $success,
        public readonly ?string $refundId = null,
        public readonly ?string $error = null,
        public readonly array   $raw = [],
    ) {}
}
