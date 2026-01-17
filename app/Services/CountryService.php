<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class CountryService
{
    public static function all(): array
    {
        return Cache::rememberForever('countries', function () {

            // ✅ Correct path based on your screenshot
            if (!Storage::disk('local')->exists('data/countries.json')) {
                return [];
            }

            $json = Storage::disk('local')->get('data/countries.json');

            $data = json_decode($json, true);

            return is_array($data) ? $data : [];
        });
    }

    public static function findByCode(string $code): ?array
    {
        return collect(self::all())
            ->firstWhere('code', strtoupper($code));
    }
}
