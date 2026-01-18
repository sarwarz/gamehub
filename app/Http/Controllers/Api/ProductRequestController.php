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
     * List product requests
     *
     * Get a paginated list of product requests.
     * You can optionally filter by status.
     *
     * @queryParam status string Optional. Filter by status (pending, approved, rejected, completed). Example: pending
     *
     * @authenticated
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Product requests fetched successfully",
     *   "data": {
     *     "current_page": 1,
     *     "data": []
     *   }
     * }
     */
    public function index(Request $request)
    {
        $requests = ProductRequest::with([
                'category', 'platform', 'type', 'region', 'language', 'worksOn'
            ])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(10);

        return $this->successResponse($requests, 'Product requests fetched successfully');
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

        $data['user_id'] = $request->user()->id;
        $data['status']  = 'pending';

        $requestItem = ProductRequest::create($data);

        return $this->successResponse($requestItem, 'Product request submitted successfully', 201);
    }

    /**
     * Get product request details
     *
     * Retrieve a single product request by ID.
     *
     * @authenticated
     *
     * @urlParam id int required Product request ID. Example: 1
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Product request details fetched",
     *   "data": {
     *     "id": 1,
     *     "title": "Windows 11 Pro OEM"
     *   }
     * }
     */
    public function show($id)
    {
        $requestItem = ProductRequest::with([
            'category', 'platform', 'type', 'region', 'language', 'worksOn'
        ])->find($id);

        if (!$requestItem) {
            return $this->errorResponse('Product request not found', 404);
        }

        return $this->successResponse($requestItem, 'Product request details fetched');
    }

    /**
     * Update product request
     *
     * Update product request information or status.
     *
     * @authenticated
     *
     * @urlParam id int required Product request ID. Example: 1
     *
     * @bodyParam title string Optional Product title.
     * @bodyParam description string Optional Product description.
     * @bodyParam source_url string Optional Source URL.
     * @bodyParam status string Optional Status (pending, approved, rejected, completed).
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Product request updated successfully"
     * }
     */
    public function update(Request $request, $id)
    {
        $requestItem = ProductRequest::find($id);

        if (!$requestItem) {
            return $this->errorResponse('Product request not found', 404);
        }

        $data = $request->validate([
            'title'       => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'source_url'  => 'nullable|url',
            'status'      => ['sometimes', Rule::in(['pending', 'approved', 'rejected', 'completed'])],
        ]);

        $requestItem->update($data);

        return $this->successResponse($requestItem, 'Product request updated successfully');
    }

    /**
     * Delete product request
     *
     * Remove a product request.
     *
     * @authenticated
     *
     * @urlParam id int required Product request ID. Example: 1
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Product request deleted successfully"
     * }
     */
    public function destroy($id)
    {
        $requestItem = ProductRequest::find($id);

        if (!$requestItem) {
            return $this->errorResponse('Product request not found', 404);
        }

        $requestItem->delete();

        return $this->successResponse(null, 'Product request deleted successfully');
    }

    /* ------------------------------
     | API Response Helpers
     |------------------------------*/

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
