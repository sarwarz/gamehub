<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Models\Setting;
use App\Models\SellerOffer;
use App\Models\SellerOfferKey;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * @group Seller Offers
 *
 * APIs for sellers to manage their product offers and license keys.
 * All endpoints require authentication and an active seller account.
 */
class SellerOfferController extends Controller
{
    /**
     * List my offers
     *
     * Get paginated list of the authenticated seller's offers
     * with product info and available key counts.
     *
     * @authenticated
     *
     * @queryParam status string Filter by status (draft, active, inactive, suspended). Example: active
     * @queryParam product_id integer Filter by product ID. Example: 5
     * @queryParam per_page integer Results per page (default 15). Example: 10
     *
     * @response 200 {"status":true,"message":"Offers fetched","data":{"current_page":1,"data":[],"total":0}}
     * @response 404 {"status":false,"message":"Seller account not found."}
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $seller = $this->getSeller();
            if (!$seller) return $this->error('Seller account not found.', 404);

            $offers = SellerOffer::where('seller_id', $seller->id)
                ->with('product:id,title,slug,image')
                ->withCount(['keys as available_keys' => fn ($q) => $q->where('status', 'available')])
                ->withCount(['keys as sold_keys' => fn ($q) => $q->where('status', 'sold')])
                ->when($request->status, fn ($q) => $q->where('status', $request->status))
                ->when($request->product_id, fn ($q) => $q->where('product_id', $request->product_id))
                ->latest()
                ->paginate(min($request->integer('per_page', 15), 50));

            return $this->success($offers, 'Offers fetched');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }

    /**
     * Show offer details
     *
     * Get detailed information about a specific offer including key statistics.
     *
     * @authenticated
     *
     * @urlParam id integer required Offer ID. Example: 1
     *
     * @response 200 {"status":true,"message":"Offer details fetched","data":{"id":1,"product":{"id":1,"title":"Windows 11 Pro"},"retail_price":"29.99","status":"active","available_keys":50,"sold_keys":120}}
     * @response 404 {"status":false,"message":"Offer not found."}
     */
    public function show($id): JsonResponse
    {
        try {
            $seller = $this->getSeller();
            if (!$seller) return $this->error('Seller account not found.', 404);

            $offer = SellerOffer::where('seller_id', $seller->id)
                ->with('product:id,title,slug,image')
                ->withCount(['keys as available_keys' => fn ($q) => $q->where('status', 'available')])
                ->withCount(['keys as sold_keys' => fn ($q) => $q->where('status', 'sold')])
                ->withCount(['keys as reserved_keys' => fn ($q) => $q->where('status', 'reserved')])
                ->find($id);

            if (!$offer) {
                return $this->error('Offer not found.', 404);
            }

            return $this->success($offer, 'Offer details fetched');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }

    /**
     * Create offer
     *
     * Create a new seller offer for a product. The offer starts as "draft"
     * by default and must be activated after adding license keys.
     *
     * @authenticated
     *
     * @bodyParam product_id integer required Product ID. Example: 1
     * @bodyParam retail_price number required Retail price. Example: 29.99
     * @bodyParam retail_acquisition_cost number optional Your cost for retail. Example: 15.00
     * @bodyParam wholesale_10_99_price number optional Wholesale price (10-99 units). Example: 25.00
     * @bodyParam wholesale_10_99_acquisition_cost number optional Your cost for wholesale 10-99. Example: 12.00
     * @bodyParam wholesale_100_plus_price number optional Wholesale price (100+ units). Example: 20.00
     * @bodyParam wholesale_100_acquisition_cost number optional Your cost for wholesale 100+. Example: 10.00
     * @bodyParam sale_mode string optional Sale mode (retail, wholesale, both). Example: both
     * @bodyParam status string optional Initial status (draft, active). Example: draft
     *
     * @response 201 {"status":true,"message":"Offer created successfully","data":{"id":1,"product_id":1,"retail_price":"29.99","status":"draft"}}
     * @response 422 {"status":false,"message":"You already have an offer for this product."}
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id'                       => 'required|integer|exists:products,id',
            'retail_price'                     => 'required|numeric|min:0.01',
            'retail_acquisition_cost'          => 'nullable|numeric|min:0',
            'wholesale_10_99_price'            => 'nullable|numeric|min:0.01',
            'wholesale_10_99_acquisition_cost' => 'nullable|numeric|min:0',
            'wholesale_100_plus_price'         => 'nullable|numeric|min:0.01',
            'wholesale_100_acquisition_cost'   => 'nullable|numeric|min:0',
            'sale_mode'                        => ['nullable', Rule::in(['retail', 'wholesale', 'both'])],
            'status'                           => ['nullable', Rule::in(['draft'])],
        ]);

        try {
            $seller = $this->getSeller();
            if (!$seller) return $this->error('Seller account not found.', 404);

            $exists = SellerOffer::where('seller_id', $seller->id)
                ->where('product_id', $data['product_id'])
                ->exists();

            if ($exists) {
                return $this->error('You already have an offer for this product.', 422);
            }

            $maxOffers = (int) Setting::get('product', 'max_offers_per_product', 0);
            if ($maxOffers > 0) {
                $totalOffers = SellerOffer::where('product_id', $data['product_id'])->count();
                if ($totalOffers >= $maxOffers) {
                    return $this->error("This product already has the maximum number of offers ({$maxOffers}).", 422);
                }
            }

            $maxProducts = (int) Setting::get('vendor', 'max_products', 0);
            if ($maxProducts > 0) {
                $sellerOfferCount = SellerOffer::where('seller_id', $seller->id)->count();
                if ($sellerOfferCount >= $maxProducts) {
                    return $this->error("You have reached the maximum number of offers ({$maxProducts}).", 422);
                }
            }

            $retailPrice = (float) $data['retail_price'];
            if (!empty($data['wholesale_10_99_price']) && (float) $data['wholesale_10_99_price'] >= $retailPrice) {
                return $this->error('Wholesale (10-99) price must be less than retail price.', 422);
            }
            if (!empty($data['wholesale_100_plus_price']) && (float) $data['wholesale_100_plus_price'] >= $retailPrice) {
                return $this->error('Wholesale (100+) price must be less than retail price.', 422);
            }

            $data['seller_id']  = $seller->id;
            $data['sale_mode']  = $data['sale_mode'] ?? 'retail';
            $data['status']     = $data['status'] ?? 'draft';

            $offer = SellerOffer::create($data);

            return $this->success($offer, 'Offer created successfully', 201);
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }

    /**
     * Update offer
     *
     * Update pricing, sale mode, or status of an existing offer.
     *
     * @authenticated
     *
     * @urlParam id integer required Offer ID. Example: 1
     *
     * @bodyParam retail_price number optional Retail price. Example: 34.99
     * @bodyParam retail_acquisition_cost number optional Cost. Example: 18.00
     * @bodyParam wholesale_10_99_price number optional Wholesale price (10-99). Example: 30.00
     * @bodyParam wholesale_10_99_acquisition_cost number optional Cost. Example: 14.00
     * @bodyParam wholesale_100_plus_price number optional Wholesale price (100+). Example: 25.00
     * @bodyParam wholesale_100_acquisition_cost number optional Cost. Example: 12.00
     * @bodyParam sale_mode string optional Sale mode (retail, wholesale, both). Example: both
     * @bodyParam status string optional Status (draft, active, inactive). Example: active
     *
     * @response 200 {"status":true,"message":"Offer updated successfully","data":{"id":1,"retail_price":"34.99","status":"active"}}
     * @response 404 {"status":false,"message":"Offer not found."}
     */
    public function update(Request $request, $id): JsonResponse
    {
        $data = $request->validate([
            'retail_price'                     => 'sometimes|numeric|min:0.01',
            'retail_acquisition_cost'          => 'nullable|numeric|min:0',
            'wholesale_10_99_price'            => 'nullable|numeric|min:0.01',
            'wholesale_10_99_acquisition_cost' => 'nullable|numeric|min:0',
            'wholesale_100_plus_price'         => 'nullable|numeric|min:0.01',
            'wholesale_100_acquisition_cost'   => 'nullable|numeric|min:0',
            'sale_mode'                        => ['sometimes', Rule::in(['retail', 'wholesale', 'both'])],
            'status'                           => ['sometimes', Rule::in(['draft', 'active', 'inactive'])],
        ]);

        try {
            $seller = $this->getSeller();
            if (!$seller) return $this->error('Seller account not found.', 404);

            $offer = SellerOffer::where('seller_id', $seller->id)->find($id);
            if (!$offer) return $this->error('Offer not found.', 404);

            if ($offer->status === 'suspended') {
                return $this->error('This offer has been suspended by an administrator.', 403);
            }

            $effectiveRetail = (float) ($data['retail_price'] ?? $offer->retail_price);
            if (!empty($data['wholesale_10_99_price']) && (float) $data['wholesale_10_99_price'] >= $effectiveRetail) {
                return $this->error('Wholesale (10-99) price must be less than retail price.', 422);
            }
            if (!empty($data['wholesale_100_plus_price']) && (float) $data['wholesale_100_plus_price'] >= $effectiveRetail) {
                return $this->error('Wholesale (100+) price must be less than retail price.', 422);
            }

            $offer->update($data);

            return $this->success($offer->fresh(), 'Offer updated successfully');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }

    /**
     * Delete offer
     *
     * Delete a seller offer. Only offers with no sold keys can be deleted.
     * Offers with sold keys should be set to "inactive" instead.
     *
     * @authenticated
     *
     * @urlParam id integer required Offer ID. Example: 1
     *
     * @response 200 {"status":true,"message":"Offer deleted successfully"}
     * @response 404 {"status":false,"message":"Offer not found."}
     * @response 422 {"status":false,"message":"Cannot delete offer with sold keys. Set to inactive instead."}
     */
    public function destroy($id): JsonResponse
    {
        try {
            $seller = $this->getSeller();
            if (!$seller) return $this->error('Seller account not found.', 404);

            $offer = SellerOffer::where('seller_id', $seller->id)->find($id);
            if (!$offer) return $this->error('Offer not found.', 404);

            if ($offer->keys()->where('status', 'sold')->exists()) {
                return $this->error('Cannot delete offer with sold keys. Set to inactive instead.', 422);
            }

            DB::transaction(function () use ($offer) {
                $offer->keys()->delete();
                $offer->delete();
            });

            return $this->success(null, 'Offer deleted successfully');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }

    /**
     * List offer keys
     *
     * Get paginated list of license keys for a specific offer.
     * Filter by key status (available, sold, reserved).
     *
     * @authenticated
     *
     * @urlParam id integer required Offer ID. Example: 1
     *
     * @queryParam status string Filter by key status. Example: available
     * @queryParam per_page integer Results per page (default 25). Example: 50
     *
     * @response 200 {"status":true,"message":"Keys fetched","data":{"current_page":1,"data":[{"id":1,"type":"text","value":"XXXX-YYYY-ZZZZ","status":"available"}],"total":50}}
     * @response 404 {"status":false,"message":"Offer not found."}
     */
    public function keys(Request $request, $id): JsonResponse
    {
        try {
            $seller = $this->getSeller();
            if (!$seller) return $this->error('Seller account not found.', 404);

            $offer = SellerOffer::where('seller_id', $seller->id)->find($id);
            if (!$offer) return $this->error('Offer not found.', 404);

            $keys = $offer->keys()
                ->when($request->status, fn ($q) => $q->where('status', $request->status))
                ->latest()
                ->paginate(min($request->integer('per_page', 25), 50));

            $keys->getCollection()->transform(function ($key) {
                if ($key->status === 'sold') {
                    $v = $key->value;
                    $key->value = strlen($v) > 8
                        ? substr($v, 0, 4) . '****' . substr($v, -4)
                        : '****';
                }
                return $key;
            });

            return $this->success($keys, 'Keys fetched');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }

    /**
     * Upload keys
     *
     * Bulk upload license keys for an offer. Keys can be text-based codes
     * or image paths. All uploaded keys start with "available" status.
     *
     * @authenticated
     *
     * @urlParam id integer required Offer ID. Example: 1
     *
     * @bodyParam keys array required List of keys to upload (max 500).
     * @bodyParam keys[].value string required The license key or image path. Example: XXXX-YYYY-ZZZZ-1234
     * @bodyParam keys[].type string optional Key type (text or image). Default: text. Example: text
     *
     * @response 201 {"status":true,"message":"15 keys uploaded successfully","data":{"uploaded":15,"total_available":65}}
     * @response 404 {"status":false,"message":"Offer not found."}
     * @response 422 {"status":false,"message":"The keys field is required."}
     */
    public function uploadKeys(Request $request, $id): JsonResponse
    {
        $request->validate([
            'keys'         => 'required|array|min:1|max:500',
            'keys.*.value' => 'required|string|max:1000',
            'keys.*.type'  => ['nullable', Rule::in(['text', 'image'])],
        ]);

        try {
            $seller = $this->getSeller();
            if (!$seller) return $this->error('Seller account not found.', 404);

            $offer = SellerOffer::where('seller_id', $seller->id)->find($id);
            if (!$offer) return $this->error('Offer not found.', 404);

            $incomingValues = collect($request->keys)->pluck('value');

            $duplicatesInDb = SellerOfferKey::where('seller_offer_id', $offer->id)
                ->whereIn('value', $incomingValues)
                ->pluck('value')
                ->toArray();

            if (count($duplicatesInDb) > 0) {
                $count = count($duplicatesInDb);
                return $this->error("{$count} duplicate key(s) already exist in this offer.", 422);
            }

            $uniqueValues = $incomingValues->unique();
            if ($uniqueValues->count() < $incomingValues->count()) {
                return $this->error('Duplicate keys found within the upload batch.', 422);
            }

            $records = [];
            foreach ($request->keys as $key) {
                $records[] = [
                    'seller_offer_id' => $offer->id,
                    'type'            => $key['type'] ?? 'text',
                    'value'           => strip_tags($key['value']),
                    'status'          => 'available',
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];
            }

            SellerOfferKey::insert($records);

            $totalAvailable = $offer->keys()->where('status', 'available')->count();

            return $this->success([
                'uploaded'        => count($records),
                'total_available' => $totalAvailable,
            ], count($records) . ' keys uploaded successfully', 201);
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }

    /**
     * Delete a key
     *
     * Remove a single license key. Only keys with "available" or "reserved"
     * status can be deleted. Sold keys cannot be removed.
     *
     * @authenticated
     *
     * @urlParam offerId integer required Offer ID. Example: 1
     * @urlParam keyId integer required Key ID. Example: 42
     *
     * @response 200 {"status":true,"message":"Key deleted successfully"}
     * @response 404 {"status":false,"message":"Key not found."}
     * @response 422 {"status":false,"message":"Sold keys cannot be deleted."}
     */
    public function deleteKey($offerId, $keyId): JsonResponse
    {
        try {
            $seller = $this->getSeller();
            if (!$seller) return $this->error('Seller account not found.', 404);

            $offer = SellerOffer::where('seller_id', $seller->id)->find($offerId);
            if (!$offer) return $this->error('Offer not found.', 404);

            $key = $offer->keys()->find($keyId);
            if (!$key) return $this->error('Key not found.', 404);

            if ($key->status === 'sold') {
                return $this->error('Sold keys cannot be deleted.', 422);
            }

            $key->delete();

            return $this->success(null, 'Key deleted successfully');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }

    private function getSeller(): ?Seller
    {
        return Seller::where('user_id', auth()->id())->first();
    }
}
