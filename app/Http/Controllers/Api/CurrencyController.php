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
     *   "status": "success",
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
        $currencies = Currency::where('is_active', true)
            ->select('id', 'code', 'name', 'symbol', 'rate', 'is_default')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $currencies
        ]);
    }

    /**
     * Get default currency
     *
     * Returns the system default currency.
     *
     * @group Currencies
     *
     * @response 200 {
     *   "status": "success",
     *   "data": {
     *     "code": "USD",
     *     "name": "US Dollar",
     *     "symbol": "$"
     *   }
     * }
     */
    public function default()
    {
        $currency = $this->currencyService->getDefaultCurrency();

        return response()->json([
            'status' => 'success',
            'data'   => $currency
        ]);
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
     *   "status": "success",
     *   "data": {
     *     "code": "EUR",
     *     "name": "Euro",
     *     "symbol": "€"
     *   }
     * }
     *
     * @response 404 {
     *   "status": "error",
     *   "message": "Not found"
     * }
     */
    public function show(string $code)
    {
        $currency = Currency::where('code', strtoupper($code))
            ->where('is_active', true)
            ->firstOrFail();

        return response()->json([
            'status' => 'success',
            'data'   => $currency
        ]);
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
     *   "status": "success",
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
     *   "status": "error",
     *   "message": "Currency EUR not found or inactive"
     * }
     */
    public function convert(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'to'     => 'required|string|size:3',
        ]);

        $amount = $validated['amount'];
        $to     = strtoupper($validated['to']);

        $converted = $this->currencyService->convert($amount, $to);

        $currency = Currency::where('code', $to)
            ->where('is_active', true)
            ->first();

        if (! $currency) {
            return response()->json([
                'status'  => 'error',
                'message' => "Currency {$to} not found or inactive"
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'amount'    => (float) $amount,
                'from'      => $this->currencyService->code(),
                'to'        => $currency->code,
                'converted' => $converted,
                'symbol'    => $currency->symbol,
                'rate'      => $currency->rate,
            ]
        ]);
    }
}
