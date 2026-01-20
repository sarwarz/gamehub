<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;

class SystemController extends Controller
{
    public function optimize(Request $request)
    {
       

        Artisan::call('optimize:clear');
        Artisan::call('optimize');

        return response()->json([
            'status' => true,
            'message' => 'Application optimized and cache cleared successfully'
        ]);
    }
}
