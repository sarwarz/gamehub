<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * @group Product Requests
 *
 * APIs for managing product requests submitted by users.
 * These endpoints are used by frontend and mobile applications.
 */
class ProductRequestController extends Controller
{
    /**
     * List my product requests
     *
     * Paginated list of the authenticated user's product requests. Optional filter by status.
     *
     * @authenticated
     *
     * @queryParam status string Filter by status (pending, approved, rejected, completed). Example: pending
     * @queryParam per_page integer Items per page (default 10). Example: 15
     *
     * @response 200 {"status":true,"message":"Product requests fetched successfully","data":{"current_page":1,"data":[]}}
     */
    public function index(Request $request)
    {
        try {
            $requests = ProductRequest::with([
                    'category', 'platform', 'type', 'region', 'language', 'worksOn'
                ])
                ->where('user_id', $request->user()->id)
                ->when($request->status, fn ($q) => $q->where('status', $request->status))
                ->latest()
                ->paginate(min($request->integer('per_page', 10), 50));

            return $this->success($requests, 'Product requests fetched successfully');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch product requests');
        }
    }

    /**
     * Submit product request
     *
     * Submit a new product request.
     *
     * @authenticated
     *
     * @bodyParam category_id int required Category ID. Example: 1
     * @bodyParam platform_id int required Platform ID. Example: 2
     * @bodyParam type_id int required Product type ID. Example: 1
     * @bodyParam region_id int required Region ID. Example: 3
     * @bodyParam language_id int required Language ID. Example: 1
     * @bodyParam works_on_id int required Works on ID. Example: 2
     * @bodyParam title string required Product title. Example: Windows 11 Pro OEM
     * @bodyParam description string required Product description.
     * @bodyParam source_url string Optional Source URL. Example: https://example.com
     *
     * @response 201 {
     *   "status": true,
     *   "message": "Product request submitted successfully",
     *   "data": {
     *     "id": 1,
     *     "status": "pending"
     *   }
     * }
     */
    public function store(Request $request)
    {
        if (!(bool) \App\Models\Setting::get('product', 'allow_product_requests', true)) {
            return $this->error('Product requests are currently disabled.', 403);
        }

        $data = $request->validate([
            'category_id' => 'required|exists:product_categories,id',
            'platform_id' => 'required|exists:product_platforms,id',
            'type_id'     => 'required|exists:product_types,id',
            'region_id'   => 'required|exists:product_regions,id',
            'language_id' => 'required|exists:product_languages,id',
            'works_on_id' => 'required|exists:product_works_ons,id',
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'source_url'  => 'nullable|url',
        ]);

        try {
            $data['user_id'] = $request->user()->id;
            $data['status']  = 'pending';

            $requestItem = ProductRequest::create($data);

            return $this->success($requestItem, 'Product request submitted successfully', 201);
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to submit product request');
        }
    }

    /**
     * Get product request details
     *
     * Retrieve one of your product requests by ID.
     *
     * @authenticated
     *
     * @urlParam id int required Product request ID. Example: 1
     *
     * @response 200 {"status":true,"message":"Product request details fetched","data":{"id":1,"title":"Windows 11 Pro OEM"}}
     * @response 404 {"status":false,"message":"Product request not found."}
     */
    public function show(Request $request, $id)
    {
        try {
            $requestItem = ProductRequest::with([
                'category', 'platform', 'type', 'region', 'language', 'worksOn'
            ])->where('user_id', $request->user()->id)->find($id);

            if (!$requestItem) {
                return $this->error('Product request not found', 404);
            }

            return $this->success($requestItem, 'Product request details fetched');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch product request details');
        }
    }

    /**
     * Update product request
     *
     * Update one of your product requests. Only pending requests should be editable.
     *
     * @authenticated
     *
     * @urlParam id int required Product request ID. Example: 1
     *
     * @bodyParam title string Optional Product title.
     * @bodyParam description string Optional Product description.
     * @bodyParam source_url string Optional Source URL.
     *
     * @response 200 {"status":true,"message":"Product request updated successfully","data":{}}
     * @response 404 {"status":false,"message":"Product request not found."}
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'title'       => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'source_url'  => 'nullable|url',
        ]);

        try {
            $requestItem = ProductRequest::where('user_id', $request->user()->id)->find($id);

            if (!$requestItem) {
                return $this->error('Product request not found', 404);
            }

            if ($requestItem->status !== 'pending') {
                return $this->error('Only pending requests can be updated.', 422);
            }

            $requestItem->update($data);

            return $this->success($requestItem, 'Product request updated successfully');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to update product request');
        }
    }

    /**
     * Delete product request
     *
     * Remove one of your product requests.
     *
     * @authenticated
     *
     * @urlParam id int required Product request ID. Example: 1
     *
     * @response 200 {"status":true,"message":"Product request deleted successfully"}
     * @response 404 {"status":false,"message":"Product request not found."}
     */
    public function destroy(Request $request, $id)
    {
        try {
            $requestItem = ProductRequest::where('user_id', $request->user()->id)->find($id);

            if (!$requestItem) {
                return $this->error('Product request not found', 404);
            }

            $requestItem->delete();

            return $this->success(null, 'Product request deleted successfully');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to delete product request');
        }
    }
}
