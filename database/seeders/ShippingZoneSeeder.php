<?php

namespace Database\Seeders;

use App\Models\ShippingZone;
use Illuminate\Database\Seeder;

class ShippingZoneSeeder extends Seeder
{
    public function run(): void
    {
        $zones = [
            ['name' => 'Inside Dhaka city',  'rate' => 60,  'delivery_time' => 'Next day, often same day', 'sort_order' => 1],
            ['name' => 'Dhaka sub-district', 'rate' => 90,  'delivery_time' => 'One to two days',          'sort_order' => 2],
            ['name' => 'Divisional cities',  'rate' => 120, 'delivery_time' => 'Two days',                 'sort_order' => 3],
            ['name' => 'Rest of Bangladesh', 'rate' => 150, 'delivery_time' => 'Two to three days',        'sort_order' => 4],
        ];

        foreach ($zones as $zone) {
            ShippingZone::firstOrCreate(['name' => $zone['name']], $zone);
        }
    }
}
