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
        try {
            return $this->success([
                'categories' => $this->categoriesData(),
                'platforms'  => $this->platformsData(),
                'types'      => $this->typesData(),
                'regions'    => $this->regionsData(),
                'languages'  => $this->languagesData(),
                'works_on'   => $this->worksOnData(),
                'developers' => $this->developersData(),
                'publishers' => $this->publishersData(),
            ], 'Product attributes fetched successfully');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch product attributes');
        }
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
        try {
            return $this->success($this->categoriesData());
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch categories');
        }
    }

    /**
     * Get product platforms
     *
     * List all platforms (e.g. Windows, Steam) for filters and product creation.
     *
     * @response 200 {"status":true,"message":"Success","data":[{"id":1,"name":"Windows","slug":"windows"}]}
     */
    public function platforms()
    {
        try {
            return $this->success($this->platformsData());
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch platforms');
        }
    }

    /**
     * Get product types
     *
     * List all product types (e.g. Retail, OEM) with commission. For filters and product creation.
     *
     * @response 200 {"status":true,"message":"Success","data":[{"id":1,"name":"Retail","slug":"retail","commission":10}]}
     */
    public function types()
    {
        try {
            return $this->success($this->typesData());
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch types');
        }
    }

    /**
     * Get product regions
     *
     * List all regions (e.g. Global, EU) for filters and product creation.
     *
     * @response 200 {"status":true,"message":"Success","data":[{"id":1,"name":"Global","slug":"global"}]}
     */
    public function regions()
    {
        try {
            return $this->success($this->regionsData());
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch regions');
        }
    }

    /**
     * Get product languages
     *
     * List all languages for filters and product creation.
     *
     * @response 200 {"status":true,"message":"Success","data":[{"id":1,"name":"English","slug":"en"}]}
     */
    public function languages()
    {
        try {
            return $this->success($this->languagesData());
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch languages');
        }
    }

    /**
     * Get supported operating systems
     *
     * List "works on" options (e.g. Windows 10, Windows 11) for filters and product creation.
     *
     * @response 200 {"status":true,"message":"Success","data":[{"id":1,"name":"Windows 10","slug":"windows-10"}]}
     */
    public function worksOn()
    {
        try {
            return $this->success($this->worksOnData());
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch works-on options');
        }
    }

    /**
     * Get product developers
     *
     * List all developers for filters and product creation.
     *
     * @response 200 {"status":true,"message":"Success","data":[{"id":1,"name":"Microsoft","slug":"microsoft"}]}
     */
    public function developers()
    {
        try {
            return $this->success($this->developersData());
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch developers');
        }
    }

    /**
     * Get product publishers
     *
     * List all publishers for filters and product creation.
     *
     * @response 200 {"status":true,"message":"Success","data":[{"id":1,"name":"Microsoft","slug":"microsoft"}]}
     */
    public function publishers()
    {
        try {
            return $this->success($this->publishersData());
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch publishers');
        }
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
        return ProductType::select('id', 'name', 'slug')->orderBy('name')->get();
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
}
