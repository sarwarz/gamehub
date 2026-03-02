<?php

use App\Services\CurrencyService;

if (! function_exists('format_currency')) {

    /**
     * Format amount with currency symbol.
     * Pass $currencyCode to format in a specific currency (e.g. 'USD'),
     * or omit to use the platform's default currency.
     */
    function format_currency($amount, ?string $currencyCode = null): string
    {
        /** @var CurrencyService $service */
        $service = app(CurrencyService::class);

        if ($currencyCode && strtoupper($currencyCode) !== strtoupper($service->code())) {
            static $symbolCache = [];
            $code = strtoupper($currencyCode);

            if (!isset($symbolCache[$code])) {
                $currency = \App\Models\Currency::where('code', $code)->first();
                $symbolCache[$code] = $currency ? ($currency->symbol ?: $currency->code) : $code;
            }

            return $service->format((float) $amount, $symbolCache[$code]);
        }

        return $service->format((float) $amount);
    }
}

if (! function_exists('get_countries')) {
    function get_countries(): array
    {
        return config('countries', []);
    }
}
