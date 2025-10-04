<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Services\CurrencyService;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    protected $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    /**
     * Get all active currencies
     */
    public function index()
    {
        $currencies = Currency::where('is_active', true)
            ->select('id','code','name','symbol','rate','is_default')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $currencies
        ]);
    }

    /**
     * Get default currency
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
     * Get currency by code (e.g. USD, EUR)
     */
    public function show($code)
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
     * Convert an amount into another currency
     * Example: /api/currencies/convert?amount=100&to=EUR
     */
    public function convert(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'to'     => 'required|string|size:3', // currency code (3 letters)
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
            'status'  => 'success',
            'data'    => [
                'amount'      => (float) $amount,
                'from'        => $this->currencyService->code(),
                'to'          => $currency->code,
                'converted'   => $converted,
                'symbol'      => $currency->symbol,
                'rate'        => $currency->rate,
            ]
        ]);
    }
}
