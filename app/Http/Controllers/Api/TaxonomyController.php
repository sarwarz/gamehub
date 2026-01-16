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

class TaxonomyController extends Controller
{
    /**
     * List product categories
     *
     * Returns all product categories.
     *
     * @group Taxonomies
     *
     * @response 200 {
     *   "status": "success",
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Operating Systems",
     *       "slug": "operating-systems"
     *     }
     *   ]
     * }
     */
    public function categories()
    {
        return response()->json([
            'status' => 'success',
            'data'   => ProductCategory::select('id','name','slug')->get()
        ]);
    }

    /**
     * List platforms
     *
     * Returns all supported platforms.
     *
     * @group Taxonomies
     *
     * @response 200 {
     *   "status": "success",
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Windows",
     *       "slug": "windows"
     *     }
     *   ]
     * }
     */
    public function platforms()
    {
        return response()->json([
            'status' => 'success',
            'data'   => ProductPlatform::select('id','name','slug')->get()
        ]);
    }

    /**
     * List product types
     *
     * Returns all product types with commission.
     *
     * @group Taxonomies
     *
     * @response 200 {
     *   "status": "success",
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Retail",
     *       "slug": "retail",
     *       "commission": 10
     *     }
     *   ]
     * }
     */
    public function types()
    {
        return response()->json([
            'status' => 'success',
            'data'   => ProductType::select('id','name','slug','commission')->get()
        ]);
    }

    /**
     * List regions
     *
     * Returns all product regions.
     *
     * @group Taxonomies
     *
     * @response 200 {
     *   "status": "success",
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Global",
     *       "slug": "global"
     *     }
     *   ]
     * }
     */
    public function regions()
    {
        return response()->json([
            'status' => 'success',
            'data'   => ProductRegion::select('id','name','slug')->get()
        ]);
    }

    /**
     * List languages
     *
     * Returns all supported languages.
     *
     * @group Taxonomies
     *
     * @response 200 {
     *   "status": "success",
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "English",
     *       "slug": "en"
     *     }
     *   ]
     * }
     */
    public function languages()
    {
        return response()->json([
            'status' => 'success',
            'data'   => ProductLanguage::select('id','name','slug')->get()
        ]);
    }

    /**
     * List supported operating systems
     *
     * Returns OS/platform compatibility (works on).
     *
     * @group Taxonomies
     *
     * @response 200 {
     *   "status": "success",
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Windows 10",
     *       "slug": "windows-10"
     *     }
     *   ]
     * }
     */
    public function worksOn()
    {
        return response()->json([
            'status' => 'success',
            'data'   => ProductWorksOn::select('id','name','slug')->get()
        ]);
    }

    /**
     * List developers
     *
     * Returns all product developers.
     *
     * @group Taxonomies
     *
     * @response 200 {
     *   "status": "success",
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Microsoft",
     *       "slug": "microsoft"
     *     }
     *   ]
     * }
     */
    public function developers()
    {
        return response()->json([
            'status' => 'success',
            'data'   => ProductDeveloper::select('id','name','slug')->get()
        ]);
    }

    /**
     * List publishers
     *
     * Returns all product publishers.
     *
     * @group Taxonomies
     *
     * @response 200 {
     *   "status": "success",
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Microsoft",
     *       "slug": "microsoft"
     *     }
     *   ]
     * }
     */
    public function publishers()
    {
        return response()->json([
            'status' => 'success',
            'data'   => ProductPublisher::select('id','name','slug')->get()
        ]);
    }
}
