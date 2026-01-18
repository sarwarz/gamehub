<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * @group Sellers
 *
 * APIs for managing seller stores and viewing seller information.
 */
class SellerController extends Controller
{
    /**
     * List sellers
     *
     * Retrieve active sellers with optional filters.
     *
     * @queryParam verified boolean Optional. Only verified sellers. Example: true
     * @queryParam country string Optional. Filter by country. Example: US
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Sellers fetched successfully",
     *   "data": {
     *     "current_page": 1,
     *     "data": []
     *   }
     * }
     */
    public function index(Request $request)
    {
        $sellers = Seller::with('user')
            ->active()
            ->when($request->verified, fn ($q) => $q->where('is_verified', true))
            ->when($request->country, fn ($q) => $q->where('country', $request->country))
            ->latest()
            ->paginate(12);

        return $this->successResponse($sellers, 'Sellers fetched successfully');
    }

    /**
     * Get seller details
     *
     * Retrieve public seller profile details.
     *
     * @urlParam id int required Seller ID. Example: 5
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Seller details fetched",
     *   "data": {
     *     "id": 5,
     *     "store_name": "Tech Store"
     *   }
     * }
     */
    public function show($id)
    {
        $seller = Seller::with([
                'user',
                'balance',
                'earnings',
                'withdrawals'
            ])
            ->active()
            ->find($id);

        if (!$seller) {
            return $this->errorResponse('Seller not found', 404);
        }

        return $this->successResponse($seller, 'Seller details fetched');
    }

    /**
     * Create seller store
     *
     * Create a seller profile for the authenticated user.
     *
     * @authenticated
     *
     * @bodyParam store_name string required Store name. Example: My Software Shop
     * @bodyParam slug string required Unique store slug. Example: my-software-shop
     * @bodyParam email string required Contact email.
     * @bodyParam phone string Optional Contact phone.
     * @bodyParam description string Optional Store description.
     * @bodyParam country string required Country. Example: US
     *
     * @response 201 {
     *   "status": true,
     *   "message": "Seller store created successfully"
     * }
     */
    public function store(Request $request)
    {
        if (Seller::where('user_id', $request->user()->id)->exists()) {
            return $this->errorResponse('Seller store already exists', 409);
        }

        $data = $request->validate([
            'store_name' => 'required|string|max:255',
            'slug'       => 'required|string|unique:sellers,slug',
            'email'      => 'required|email',
            'phone'      => 'nullable|string|max:30',
            'description'=> 'nullable|string',
            'country'    => 'required|string|max:100',
        ]);

        $data['user_id'] = $request->user()->id;
        $data['status']  = 'pending';
        $data['is_verified'] = false;

        $seller = Seller::create($data);

        return $this->successResponse($seller, 'Seller store created successfully', 201);
    }

    /**
     * Update seller store
     *
     * Update seller store information.
     *
     * @authenticated
     *
     * @urlParam id int required Seller ID. Example: 5
     *
     * @bodyParam store_name string Optional Store name.
     * @bodyParam description string Optional Store description.
     * @bodyParam phone string Optional Phone number.
     * @bodyParam website string Optional Website URL.
     * @bodyParam status string Optional (admin only).
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Seller updated successfully"
     * }
     */
    public function update(Request $request, $id)
    {
        $seller = Seller::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$seller) {
            return $this->errorResponse('Unauthorized or seller not found', 403);
        }

        $data = $request->validate([
            'store_name'  => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'phone'       => 'nullable|string|max:30',
            'website'     => 'nullable|url',
            'status'      => ['sometimes', Rule::in(['pending', 'active', 'suspended'])],
        ]);

        $seller->update($data);

        return $this->successResponse($seller, 'Seller updated successfully');
    }

    /* --------------------------------
     | API Response Helpers
     |-------------------------------- */

    protected function successResponse($data, $message = 'Success', $code = 200)
    {
        return response()->json([
            'status'  => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    protected function errorResponse($message, $code = 400)
    {
        return response()->json([
            'status'  => false,
            'message' => $message,
        ], $code);
    }
}
