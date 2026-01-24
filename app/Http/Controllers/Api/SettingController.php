<?php

namespace App\Http\Controllers\Api;

use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class SettingController
{
    public function index(): JsonResponse
    {
        $settings = Setting::all()
            ->groupBy('group')
            ->map(fn ($items) =>
                $items->pluck('value', 'key')
            );

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }
}
