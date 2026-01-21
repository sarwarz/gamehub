<?php

use App\Services\CurrencyService;

if (! function_exists('format_currency')) {

    /**
     * Format amount using default currency symbol
     *
     * @param float|int $amount
     * @param int $decimals
     * @return string
     */
    function format_currency($amount, int $decimals = 2): string
    {
        /** @var CurrencyService $service */
        $service = app(CurrencyService::class);

        $currency = $service->getDefaultCurrency();

        $formatted = number_format(
            (float) $amount,
            $decimals,
            '.',   // decimal separator
            ','    // thousand separator
        );

        if (! $currency) {
            return $formatted;
        }

        // Use symbol if available, otherwise fallback to code
        $symbol = $currency->symbol ?: $currency->code;

        // Symbol before amount (recommended standard)
        return "{$symbol}{$formatted}";
    }
}

if (! function_exists('get_countries')) {
    function get_countries(): array
    {
        return config('countries', []);
    }
}
