<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use App\Models\ProductPlatform;
use App\Models\ProductType;
use App\Models\ProductRegion;
use App\Models\ProductLanguage;
use App\Models\ProductWorksOn;
use App\Models\ProductDeveloper;
use App\Models\ProductPublisher;

/**
 * @group Product Attributes
 *
 * Product-related attributes used for filters,
 * dropdowns, and product creation.
 *
 * @unauthenticated
 */
class ProductAttributeController extends Controller
{
    /**
     * Get all product attributes
     *
     * Fetch all product attributes in a single request.
     * Recommended for frontend initialization.
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Product attributes fetched successfully",
     *   "data": {
     *     "categories": [{"id":1,"name":"Operating Systems","slug":"operating-systems"}],
     *     "platforms": [{"id":1,"name":"Windows","slug":"windows"}],
     *     "types": [{"id":1,"name":"Retail","slug":"retail","commission":10}],
     *     "regions": [{"id":1,"name":"Global","slug":"global"}],
     *     "languages": [{"id":1,"name":"English","slug":"en"}],
     *     "works_on": [{"id":1,"name":"Windows 10","slug":"windows-10"}],
     *     "developers": [{"id":1,"name":"Microsoft","slug":"microsoft"}],
     *     "publishers": [{"id":1,"name":"Microsoft","slug":"microsoft"}]
     *   }
     * }
     */
    public function index()
    {
        return $this->successResponse([
            'categories' => $this->categoriesData(),
            'platforms'  => $this->platformsData(),
            'types'      => $this->typesData(),
            'regions'    => $this->regionsData(),
            'languages'  => $this->languagesData(),
            'works_on'   => $this->worksOnData(),
            'developers' => $this->developersData(),
            'publishers' => $this->publishersData(),
        ], 'Product attributes fetched successfully');
    }

    /**
     * Get product categories
     *
     * @response 200 {
     *   "status": true,
     *   "data": [{"id":1,"name":"Operating Systems","slug":"operating-systems"}]
     * }
     */
    public function categories()
    {
        return $this->successResponse($this->categoriesData());
    }

    /**
     * Get product platforms
     */
    public function platforms()
    {
        return $this->successResponse($this->platformsData());
    }

    /**
     * Get product types
     */
    public function types()
    {
        return $this->successResponse($this->typesData());
    }

    /**
     * Get product regions
     */
    public function regions()
    {
        return $this->successResponse($this->regionsData());
    }

    /**
     * Get product languages
     */
    public function languages()
    {
        return $this->successResponse($this->languagesData());
    }

    /**
     * Get supported operating systems
     */
    public function worksOn()
    {
        return $this->successResponse($this->worksOnData());
    }

    /**
     * Get product developers
     */
    public function developers()
    {
        return $this->successResponse($this->developersData());
    }

    /**
     * Get product publishers
     */
    public function publishers()
    {
        return $this->successResponse($this->publishersData());
    }

    /* -----------------------------
     | Data Providers
     |------------------------------*/

    protected function categoriesData()
    {
        return ProductCategory::select('id', 'name', 'slug')->orderBy('name')->get();
    }

    protected function platformsData()
    {
        return ProductPlatform::select('id', 'name', 'slug')->orderBy('name')->get();
    }

    protected function typesData()
    {
        return ProductType::select('id', 'name', 'slug', 'commission')->orderBy('name')->get();
    }

    protected function regionsData()
    {
        return ProductRegion::select('id', 'name', 'slug')->orderBy('name')->get();
    }

    protected function languagesData()
    {
        return ProductLanguage::select('id', 'name', 'slug')->orderBy('name')->get();
    }

    protected function worksOnData()
    {
        return ProductWorksOn::select('id', 'name', 'slug')->orderBy('name')->get();
    }

    protected function developersData()
    {
        return ProductDeveloper::select('id', 'name', 'slug')->orderBy('name')->get();
    }

    protected function publishersData()
    {
        return ProductPublisher::select('id', 'name', 'slug')->orderBy('name')->get();
    }

    /* -----------------------------
     | API Response Helper
     |------------------------------*/

    protected function successResponse($data, $message = 'Success', $code = 200)
    {
        return response()->json([
            'status'  => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }
}
