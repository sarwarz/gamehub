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
    public function categories()
    {
        return response()->json([
            'status' => 'success',
            'data'   => ProductCategory::select('id','name','slug')->get()
        ]);
    }

    public function platforms()
    {
        return response()->json([
            'status' => 'success',
            'data'   => ProductPlatform::select('id','name','slug')->get()
        ]);
    }

    public function types()
    {
        return response()->json([
            'status' => 'success',
            'data'   => ProductType::select('id','name','slug','commission')->get()
        ]);
    }

    public function regions()
    {
        return response()->json([
            'status' => 'success',
            'data'   => ProductRegion::select('id','name','slug')->get()
        ]);
    }

    public function languages()
    {
        return response()->json([
            'status' => 'success',
            'data'   => ProductLanguage::select('id','name','slug')->get()
        ]);
    }

    public function worksOn()
    {
        return response()->json([
            'status' => 'success',
            'data'   => ProductWorksOn::select('id','name','slug')->get()
        ]);
    }

    public function developers()
    {
        return response()->json([
            'status' => 'success',
            'data'   => ProductDeveloper::select('id','name','slug')->get()
        ]);
    }

    public function publishers()
    {
        return response()->json([
            'status' => 'success',
            'data'   => ProductPublisher::select('id','name','slug')->get()
        ]);
    }
}
