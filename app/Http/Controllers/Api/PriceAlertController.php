<?php

namespace App\Http\Controllers\Api;

use App\Models\PriceAlert;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

/**
 * @group Price & Stock Alerts
 *
 * APIs for managing price drop and back-in-stock alerts.
 * Users subscribe to notifications when a product's price falls
 * below a target or when an out-of-stock product becomes available.
 *
 * All endpoints require authentication.
 */
class PriceAlertController extends Controller
{
    /**
     * List my alerts
     *
     * Get all active and inactive price/stock alerts for the authenticated user.
     *
     * @authenticated
     *
     * @queryParam type string Filter by type: price_drop, back_in_stock. Example: price_drop
     * @queryParam is_active boolean Filter by active status. Example: true
     *
     * @response 200 {"status":true,"message":"Alerts fetched","data":[{"id":1,"product":{"id":5,"title":"Windows 11 Pro","slug":"windows-11-pro","image":"..."},"type":"price_drop","target_price":"25.00","is_active":true,"notified_at":null}]}
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = PriceAlert::where('user_id', $request->user()->id)
                ->with('product:id,title,slug,image');

            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            if ($request->filled('is_active')) {
                $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
            }

            $alerts = $query->latest()->get()->map(fn ($a) => [
                'id'           => $a->id,
                'product'      => $a->product?->only(['id', 'title', 'slug', 'image']),
                'type'         => $a->type,
                'target_price' => $a->target_price,
                'is_active'    => $a->is_active,
                'notified_at'  => $a->notified_at?->toISOString(),
                'created_at'   => $a->created_at?->toISOString(),
            ]);

            return $this->success($alerts, 'Alerts fetched');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }

    /**
     * Create alert
     *
     * Subscribe to a price drop or back-in-stock alert for a product.
     * Only one alert per product per type is allowed.
     *
     * @authenticated
     *
     * @bodyParam product_id integer required Product ID. Example: 5
     * @bodyParam type string required Alert type: price_drop or back_in_stock. Example: price_drop
     * @bodyParam target_price number Required for price_drop type. The price you want to be notified at. Example: 25.00
     *
     * @response 201 {"status":true,"message":"Alert created","data":{"id":1,"type":"price_drop","target_price":"25.00","is_active":true}}
     * @response 409 {"status":false,"message":"You already have this alert"}
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_id'   => 'required|integer|exists:products,id',
            'type'         => 'required|in:price_drop,back_in_stock',
            'target_price' => 'required_if:type,price_drop|nullable|numeric|min:0.01',
        ]);

        try {
            $exists = PriceAlert::where('user_id', $request->user()->id)
                ->where('product_id', $request->product_id)
                ->where('type', $request->type)
                ->exists();

            if ($exists) {
                return $this->error('You already have this alert for this product.', 409);
            }

            $alert = PriceAlert::create([
                'user_id'      => $request->user()->id,
                'product_id'   => $request->product_id,
                'type'         => $request->type,
                'target_price' => $request->type === 'price_drop' ? $request->target_price : null,
                'is_active'    => true,
            ]);

            return $this->success($alert, 'Alert created', 201);
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }

    /**
     * Update alert
     *
     * Update the target price or active status of an existing alert.
     *
     * @authenticated
     *
     * @urlParam id integer required Alert ID. Example: 1
     *
     * @bodyParam target_price number New target price. Example: 20.00
     * @bodyParam is_active boolean Enable or disable alert. Example: false
     *
     * @response 200 {"status":true,"message":"Alert updated","data":{}}
     * @response 404 {"status":false,"message":"Alert not found"}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $alert = PriceAlert::where('user_id', $request->user()->id)->find($id);
            if (!$alert) {
                return $this->error('Alert not found', 404);
            }

            $request->validate([
                'target_price' => 'nullable|numeric|min:0.01',
                'is_active'    => 'sometimes|boolean',
            ]);

            $alert->update($request->only(['target_price', 'is_active']));

            return $this->success($alert->fresh(), 'Alert updated');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }

    /**
     * Delete alert
     *
     * Remove a price/stock alert subscription.
     *
     * @authenticated
     *
     * @urlParam id integer required Alert ID. Example: 1
     *
     * @response 200 {"status":true,"message":"Alert removed"}
     * @response 404 {"status":false,"message":"Alert not found"}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $alert = PriceAlert::where('user_id', $request->user()->id)->find($id);
            if (!$alert) {
                return $this->error('Alert not found', 404);
            }

            $alert->delete();

            return $this->success(null, 'Alert removed');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }

    /**
     * Check alert status
     *
     * Check if the user has an active alert for a specific product.
     *
     * @authenticated
     *
     * @urlParam productId integer required Product ID. Example: 5
     *
     * @queryParam type string Alert type to check. Example: price_drop
     *
     * @response 200 {"status":true,"message":"Alert status","data":{"has_price_drop":true,"has_back_in_stock":false,"alerts":[{"id":1,"type":"price_drop","target_price":"25.00","is_active":true}]}}
     */
    public function check(Request $request, int $productId): JsonResponse
    {
        try {
            $alerts = PriceAlert::where('user_id', $request->user()->id)
                ->where('product_id', $productId)
                ->where('is_active', true)
                ->get();

            return $this->success([
                'has_price_drop'    => $alerts->where('type', 'price_drop')->isNotEmpty(),
                'has_back_in_stock' => $alerts->where('type', 'back_in_stock')->isNotEmpty(),
                'alerts'            => $alerts->map(fn ($a) => [
                    'id'           => $a->id,
                    'type'         => $a->type,
                    'target_price' => $a->target_price,
                    'is_active'    => $a->is_active,
                ]),
            ], 'Alert status');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }
}
