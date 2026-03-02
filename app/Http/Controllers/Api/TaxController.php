<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Models\Tax;
use Illuminate\Http\Request;

/**
 * @group Taxes
 *
 * APIs for retrieving and calculating tax rules
 * for checkout and seller-specific configurations.
 */
class TaxController extends Controller
{
    /**
     * List applicable taxes
     *
     * Retrieve active taxes based on location and seller.
     *
     * @queryParam seller_id int Optional Seller ID. Example: 5
     * @queryParam country string Optional Country code. Example: US
     * @queryParam state string Optional State. Example: CA
     * @queryParam city string Optional City. Example: Los Angeles
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Taxes fetched successfully",
     *   "data": []
     * }
     */
    public function index(Request $request)
    {
        $request->validate([
            'seller_id' => 'nullable|integer',
        ]);

        try {
            $taxes = Tax::where('is_active', true)
                ->when($request->seller_id, function ($q) use ($request) {
                    $q->where(function ($q) use ($request) {
                        $q->whereNull('seller_id')
                          ->orWhere('seller_id', $request->seller_id);
                    });
                })
                ->when($request->country, fn ($q) => $q->where('country', $request->country))
                ->when($request->state, fn ($q) => $q->where('state', $request->state))
                ->when($request->city, fn ($q) => $q->where('city', $request->city))
                ->orderBy('priority')
                ->get();

            return $this->success($taxes, 'Taxes fetched successfully');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch taxes', 500);
        }
    }

    /**
     * Calculate tax amount
     *
     * Calculate applicable tax amount for an order.
     *
     * @authenticated
     *
     * @bodyParam amount number required Order amount. Example: 100
     * @bodyParam seller_id int Optional Seller ID. Example: 5
     * @bodyParam country string required Country code. Example: US
     * @bodyParam state string Optional State. Example: CA
     * @bodyParam city string Optional City. Example: Los Angeles
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Tax calculated successfully",
     *   "data": {
     *     "tax_total": 12.5
     *   }
     * }
     */
    public function calculate(Request $request)
    {
        $data = $request->validate([
            'amount'    => 'required|numeric|min:0',
            'seller_id' => 'nullable|integer|exists:sellers,id',
            'country'   => 'required|string|max:100',
            'state'     => 'nullable|string|max:100',
            'city'      => 'nullable|string|max:100',
        ]);

        try {
            $taxes = Tax::where('is_active', true)
                ->where(function ($q) use ($data) {
                    $q->whereNull('seller_id')
                      ->orWhere('seller_id', $data['seller_id'] ?? null);
                })
                ->where('country', $data['country'])
                ->when($data['state'] ?? null, fn ($q) => $q->where('state', $data['state']))
                ->when($data['city'] ?? null, fn ($q) => $q->where('city', $data['city']))
                ->orderBy('priority')
                ->get();

            $taxTotal = 0;

            foreach ($taxes as $tax) {
                $base = $tax->is_compound ? ($data['amount'] + $taxTotal) : $data['amount'];

                $taxAmount = $tax->type === 'percent'
                    ? ($base * $tax->rate / 100)
                    : $tax->rate;

                $taxTotal += round($taxAmount, 2);
            }

            return $this->success([
                'tax_total' => round($taxTotal, 2),
                'taxes'     => $taxes,
            ], 'Tax calculated successfully');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to calculate tax', 500);
        }
    }

    /**
     * Seller tax rules
     *
     * Retrieve tax rules created by the authenticated seller.
     *
     * @authenticated
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Seller taxes fetched successfully",
     *   "data": []
     * }
     */
    public function sellerTaxes(Request $request)
    {
        try {
            $seller = Seller::where('user_id', $request->user()->id)->first();

            if (!$seller) {
                return $this->error('Seller account not found', 404);
            }

            $taxes = Tax::where('seller_id', $seller->id)
                ->latest()
                ->get();

            return $this->success($taxes, 'Seller taxes fetched successfully');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch seller taxes', 500);
        }
    }
}
