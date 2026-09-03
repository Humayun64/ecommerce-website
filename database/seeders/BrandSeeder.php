<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            ['name' => 'Anua',       'country' => 'South Korea', 'sort_order' => 1],
            ['name' => 'SKIN1004',   'country' => 'South Korea', 'sort_order' => 2],
            ['name' => 'AXIS-Y',     'country' => 'South Korea', 'sort_order' => 3],
            ['name' => 'DR.ALTHEA',  'country' => 'South Korea', 'sort_order' => 4],
            ['name' => 'Dabo',       'country' => 'South Korea', 'sort_order' => 5],
            ['name' => 'AMJR Picks', 'country' => null,          'sort_order' => 6],
        ];

        foreach ($brands as $brand) {
            Brand::updateOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($brand['name'])],
                $brand
            );
        }
    }
}
