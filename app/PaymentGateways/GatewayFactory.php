<?php

namespace App\PaymentGateways;

use App\Contracts\PaymentGatewayInterface;

class GatewayFactory
{
    protected array $gateways = [
        'stripe'    => StripeGateway::class,
        'paypal'    => PayPalGateway::class,
        'cryptomus' => CryptomusGateway::class,
    ];

    public function make(string $code): PaymentGatewayInterface
    {
        $class = $this->gateways[$code] ?? null;

        if (!$class) {
            throw new \InvalidArgumentException("Unsupported payment gateway: {$code}");
        }

        return app($class);
    }

    public function supports(string $code): bool
    {
        return isset($this->gateways[$code]);
    }
}
