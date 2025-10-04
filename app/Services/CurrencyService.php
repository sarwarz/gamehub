<?php

namespace App\Services;

use App\Models\Currency;
use Illuminate\Support\Facades\Cache;

class CurrencyService
{
    /**
     * Get default currency (cached for performance).
     */
    public function getDefaultCurrency(): ?Currency
    {
        return Cache::rememberForever('default_currency', function () {
            return Currency::where('is_default', true)->first();
        });
    }

    /**
     * Get default currency code (e.g. USD).
     */
    public function code(): string
    {
        return optional($this->getDefaultCurrency())->code ?? 'USD';
    }

    /**
     * Get default currency symbol (e.g. $).
     */
    public function symbol(): string
    {
        return optional($this->getDefaultCurrency())->symbol ?? '$';
    }

    /**
     * Convert an amount from default to another currency.
     */
    public function convert(float $amount, string $toCurrency): float
    {
        $currency = Currency::where('code', $toCurrency)->first();

        if (! $currency) {
            return $amount; // fallback
        }

        return round($amount * $currency->rate, 2);
    }

    /**
     * Clear cache when updating currencies.
     */
    public function clearCache(): void
    {
        Cache::forget('default_currency');
    }
}
