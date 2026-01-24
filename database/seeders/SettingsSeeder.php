<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    public function run()
    {
        Setting::set('general', 'site_name', 'My E-Commerce');
        Setting::set('general', 'currency', 'USD');

        Setting::set('ui', 'logo', '/logo.png');
        Setting::set('ui', 'primary_color', '#0d6efd');

        Setting::set('seo', 'meta_title', 'Best Online Shop');
        Setting::set('seo', 'meta_description', 'Buy digital products online');
    }
}
