<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\DeliveryRate;
use App\Models\DeliveryTier;
use App\Models\Product;
use App\Models\Setting;
use App\Models\ShippingZone;
use Illuminate\Database\Seeder;

class DeliverySeeder extends Seeder
{
    public function run(): void
    {
        $tiers = [
            ['name' => 'Small',  'icon' => '📦', 'description' => 'Serums, creams, cleansers, cables', 'sort_order' => 1, 'is_default' => true],
            ['name' => 'Medium', 'icon' => '📦', 'description' => 'Power banks, torches, boxed sets',   'sort_order' => 2, 'is_default' => false],
            ['name' => 'Heavy',  'icon' => '🏋', 'description' => 'Bulk orders and anything oversized', 'sort_order' => 3, 'is_default' => false],
        ];

        foreach ($tiers as $tier) {
            DeliveryTier::firstOrCreate(['name' => $tier['name']], $tier);
        }

        // Starting rates. He changes these on the Delivery page.
        $matrix = [
            'Inside Dhaka city'  => ['Small' => 60,  'Medium' => 100, 'Heavy' => 250],
            'Dhaka sub-district' => ['Small' => 90,  'Medium' => 140, 'Heavy' => 320],
            'Divisional cities'  => ['Small' => 120, 'Medium' => 180, 'Heavy' => 450],
            'Rest of Bangladesh' => ['Small' => 150, 'Medium' => 220, 'Heavy' => 500],
        ];

        foreach ($matrix as $zoneName => $byTier) {
            $zone = ShippingZone::where('name', $zoneName)->first();

            if (! $zone) {
                continue;
            }

            foreach ($byTier as $tierName => $rate) {
                $tier = DeliveryTier::where('name', $tierName)->first();

                if ($tier) {
                    DeliveryRate::firstOrCreate(
                        ['delivery_tier_id' => $tier->id, 'shipping_zone_id' => $zone->id],
                        ['rate' => $rate]
                    );
                }
            }
        }

        Setting::put([
            'delivery_multi_rule'     => Setting::get('delivery_multi_rule', 'highest'),
            'delivery_extra_per_item' => Setting::get('delivery_extra_per_item', 20),
        ]);

        $this->assignExistingProducts();
    }

    /** Gadgets are bulkier than a 30ml bottle, so start them a band up. */
    private function assignExistingProducts(): void
    {
        $small  = DeliveryTier::where('name', 'Small')->first();
        $medium = DeliveryTier::where('name', 'Medium')->first();

        if (! $small || ! $medium) {
            return;
        }

        $gadgetIds = Category::where('slug', 'gadgets')
            ->orWhere('parent_id', Category::where('slug', 'gadgets')->value('id'))
            ->pluck('id');

        Product::whereNull('delivery_tier_id')
            ->whereIn('category_id', $gadgetIds)
            ->update(['delivery_tier_id' => $medium->id]);

        Product::whereNull('delivery_tier_id')->update(['delivery_tier_id' => $small->id]);
    }
}
