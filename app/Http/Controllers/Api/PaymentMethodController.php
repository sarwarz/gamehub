<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @group Payment Methods
 *
 * APIs for retrieving available payment methods
 * during checkout and payment selection.
 *
 * @unauthenticated
 */
class PaymentMethodController extends Controller
{
    /**
     * List payment methods
     *
     * Retrieve enabled payment methods.
     * Supports filtering by country, currency, and type.
     *
     * @queryParam country string Optional. Country code. Example: US
     * @queryParam currency string Optional. Currency code. Example: USD
     * @queryParam type string Optional. online, offline, wallet. Example: online
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Payment methods fetched successfully",
     *   "data": []
     * }
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $methods = PaymentMethod::where('is_enabled', true)
                ->when($request->country, fn ($q) =>
                    $q->where(function ($q) use ($request) {
                        $q->whereNull('country')
                          ->orWhere('country', $request->country);
                    })
                )
                ->when($request->currency, fn ($q) =>
                    $q->where(function ($q) use ($request) {
                        $q->whereNull('currency')
                          ->orWhere('currency', $request->currency);
                    })
                )
                ->when($request->type, fn ($q) => $q->where('type', $request->type))
                ->orderBy('sort_order')
                ->get();

            return $this->success(
                $methods->map(fn ($method) => $this->transform($method)),
                'Payment methods fetched successfully'
            );
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Unable to fetch payment methods.', 500);
        }
    }

    /**
     * Get payment method
     *
     * Retrieve payment method details by code.
     *
     * @urlParam code string required Payment method code. Example: stripe
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Payment method fetched successfully",
     *   "data": {
     *     "name": "Stripe"
     *   }
     * }
     */
    public function show(string $code): JsonResponse
    {
        try {
            $method = PaymentMethod::where('code', $code)
                ->where('is_enabled', true)
                ->first();

            if (!$method) {
                return $this->error('Payment method not found', 404);
            }

            return $this->success(
                $this->transform($method),
                'Payment method fetched successfully'
            );
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Unable to fetch payment method.', 500);
        }
    }

    /* --------------------------------
     | Data Transformer
     |-------------------------------- */

    protected function transform(PaymentMethod $method): array
    {
        return [
            'name'      => $method->name,
            'code'      => $method->code,
            'type'      => $method->type,
            'rate'      => $method->rate,
            'country'   => $method->country,
            'currency'  => $method->currency,
        ];
    }
}
