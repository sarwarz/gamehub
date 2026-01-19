<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductLabel;

class ProductLabelsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $labels = [
            [
                'name'       => 'New',
                'bg_color'   => '#198754',
                'text_color' => '#ffffff',
                'status'     => 'active',
            ],
            [
                'name'       => 'Hot',
                'bg_color'   => '#dc3545',
                'text_color' => '#ffffff',
                'status'     => 'active',
            ],
            [
                'name'       => 'Sale',
                'bg_color'   => '#fd7e14',
                'text_color' => '#ffffff',
                'status'     => 'active',
            ],
            [
                'name'       => 'Limited',
                'bg_color'   => '#6f42c1',
                'text_color' => '#ffffff',
                'status'     => 'inactive',
            ],
        ];

        foreach ($labels as $label) {
            ProductLabel::updateOrCreate(
                ['name' => $label['name']], // prevent duplicates
                $label
            );
        }
    }
}
