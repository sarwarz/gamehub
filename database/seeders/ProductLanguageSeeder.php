<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductLanguage;
use Illuminate\Support\Str;

class ProductLanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = [
            ['name' => 'English', 'code' => 'en'],
            ['name' => 'French', 'code' => 'fr'],
            ['name' => 'German', 'code' => 'de'],
            ['name' => 'Spanish', 'code' => 'es'],
            ['name' => 'Italian', 'code' => 'it'],
            ['name' => 'Portuguese', 'code' => 'pt'],
            ['name' => 'Russian', 'code' => 'ru'],
            ['name' => 'Chinese (Simplified)', 'code' => 'zh-CN'],
            ['name' => 'Chinese (Traditional)', 'code' => 'zh-TW'],
            ['name' => 'Japanese', 'code' => 'ja'],
            ['name' => 'Korean', 'code' => 'ko'],
            ['name' => 'Arabic', 'code' => 'ar'],
            ['name' => 'Turkish', 'code' => 'tr'],
            ['name' => 'Hindi', 'code' => 'hi'],
        ];

        foreach ($languages as $lang) {
            ProductLanguage::firstOrCreate(
                ['slug' => Str::slug($lang['name'])],
                [
                    'name'   => $lang['name'],
                    'code'   => $lang['code'],
                    'status' => 'active',
                ]
            );
        }
    }
}
