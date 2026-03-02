<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Services\CurrencyService;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    protected CurrencyService $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    /**
     * List active currencies
     *
     * Returns all active currencies supported by the platform.
     *
     * @group Currencies
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Currencies fetched successfully",
     *   "data": [
     *     {
     *       "id": 1,
     *       "code": "USD",
     *       "name": "US Dollar",
     *       "symbol": "$",
     *       "rate": 1,
     *       "is_default": true
     *     }
     *   ]
     * }
     */
    public function index()
    {
        try {
            $currencies = Currency::where('is_active', true)
                ->select('id', 'code', 'name', 'symbol', 'rate', 'is_default')
                ->get();

            return $this->success($currencies, 'Currencies fetched successfully');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch currencies', 500);
        }
    }

    /**
     * Get default currency
     *
     * Returns the system default currency.
     *
     * @group Currencies
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Default currency fetched successfully",
     *   "data": {
     *     "code": "USD",
     *     "name": "US Dollar",
     *     "symbol": "$"
     *   }
     * }
     */
    public function default()
    {
        try {
            $currency = $this->currencyService->getDefaultCurrency();

            if (!$currency) {
                return $this->error('No default currency configured', 404);
            }

            return $this->success($currency, 'Default currency fetched successfully');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch default currency', 500);
        }
    }

    /**
     * Get currency by code
     *
     * Fetch a single active currency by its ISO code.
     *
     * @group Currencies
     *
     * @urlParam code string required ISO currency code. Example: USD
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Currency fetched successfully",
     *   "data": {
     *     "code": "EUR",
     *     "name": "Euro",
     *     "symbol": "€"
     *   }
     * }
     *
     * @response 404 {
     *   "status": false,
     *   "message": "Currency not found"
     * }
     */
    public function show(string $code)
    {
        try {
            $currency = Currency::where('code', strtoupper($code))
                ->where('is_active', true)
                ->first();

            if (!$currency) {
                return $this->error('Currency not found', 404);
            }

            return $this->success($currency, 'Currency fetched successfully');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch currency', 500);
        }
    }

    /**
     * Convert currency
     *
     * Converts an amount from the default currency to a target currency.
     *
     * @group Currencies
     *
     * @queryParam amount number required Amount to convert. Example: 100
     * @queryParam to string required Target currency code (3 letters). Example: EUR
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Currency converted successfully",
     *   "data": {
     *     "amount": 100,
     *     "from": "USD",
     *     "to": "EUR",
     *     "converted": 92.5,
     *     "symbol": "€",
     *     "rate": 0.925
     *   }
     * }
     *
     * @response 404 {
     *   "status": false,
     *   "message": "Currency EUR not found or inactive"
     * }
     */
    public function convert(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'to'     => 'required|string|size:3',
        ]);

        try {
            $amount = $validated['amount'];
            $to     = strtoupper($validated['to']);

            $currency = Currency::where('code', $to)
                ->where('is_active', true)
                ->first();

            if (!$currency) {
                return $this->error("Currency {$to} not found or inactive", 404);
            }

            $converted = $this->currencyService->convert($amount, $to);

            return $this->success([
                'amount'    => (float) $amount,
                'from'      => $this->currencyService->code(),
                'to'        => $currency->code,
                'converted' => $converted,
                'symbol'    => $currency->symbol,
                'rate'      => $currency->rate,
            ], 'Currency converted successfully');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to convert currency', 500);
        }
    }
}
