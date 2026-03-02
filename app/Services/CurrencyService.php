<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class CurrencyService
{
    public function getDefaultCurrency(): ?Currency
    {
        return Cache::rememberForever('default_currency', function () {
            return Currency::where('is_default', true)->first();
        });
    }

    public function code(): string
    {
        return optional($this->getDefaultCurrency())->code ?? 'USD';
    }

    public function symbol(): string
    {
        return optional($this->getDefaultCurrency())->symbol ?? '$';
    }

    public function convert(float $amount, string $toCurrency): float
    {
        $currency = Currency::where('code', $toCurrency)->first();

        if (! $currency) {
            return $amount;
        }

        return round($amount * $currency->rate, 2);
    }

    /**
     * Format an amount using currency_locale settings.
     */
    public function format(float $amount, ?string $currencySymbol = null): string
    {
        $locale = Setting::group('currency_locale');

        $decimals = (int) ($locale['decimal_places'] ?? 2);
        $decSep = $locale['decimal_separator'] ?? '.';
        $thousandsSep = $locale['thousands_separator'] ?? ',';
        $position = $locale['currency_position'] ?? 'before';
        $symbol = $currencySymbol ?? $this->symbol();

        $formatted = number_format($amount, $decimals, $decSep, $thousandsSep);

        return $position === 'after'
            ? "{$formatted}{$symbol}"
            : "{$symbol}{$formatted}";
    }

    /**
     * Get locale config for frontend use.
     */
    public function localeConfig(): array
    {
        $locale = Setting::group('currency_locale');

        return [
            'decimal_places'      => (int) ($locale['decimal_places'] ?? 2),
            'decimal_separator'   => $locale['decimal_separator'] ?? '.',
            'thousands_separator' => $locale['thousands_separator'] ?? ',',
            'currency_position'   => $locale['currency_position'] ?? 'before',
            'rtl_enabled'         => !empty($locale['rtl_enabled']),
            'default_language'    => $locale['default_language'] ?? 'en',
        ];
    }

    public function clearCache(): void
    {
        Cache::forget('default_currency');
    }
}
