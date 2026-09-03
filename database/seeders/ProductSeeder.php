<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::pluck('id', 'slug');
        $brands     = Brand::pluck('id', 'slug');

        foreach ($this->products() as $row) {
            $variants = $row['variants'] ?? [];
            unset($row['variants']);

            $row['category_id']  = $categories[$row['category']] ?? null;
            $row['brand_id']     = $brands[$row['brand']] ?? null;
            $row['has_variants'] = count($variants) > 0;
            unset($row['category'], $row['brand']);

            $product = Product::updateOrCreate(['sku' => $row['sku']], $row);

            $product->variants()->delete();

            foreach ($variants as $i => $variant) {
                $product->variants()->create([
                    'name'          => $variant['name'],
                    'sku'           => $product->sku . '-' . ($i + 1),
                    'price'         => $variant['price'],
                    'compare_price' => $variant['compare_price'] ?? null,
                    'stock'         => $variant['stock'],
                    'sort_order'    => $i + 1,
                ]);
            }
        }
    }

    /**
     * Launch catalog. Prices and stock are placeholders —
     * replace them in the admin panel with real figures.
     */
    private function products(): array
    {
        return [
            [
                'name' => 'Anua Niacinamide 10% + TXA 4% Serum',
                'slug' => 'anua-niacinamide-10-txa-4-serum',
                'sku' => 'ANU-NIA-01',
                'brand' => 'anua', 'category' => 'serums',
                'origin' => 'South Korea', 'size_label' => '15 ml / 30 ml',
                'short_description' => 'Brightening serum for dark spots and uneven tone.',
                'description' => 'Niacinamide at 10% paired with tranexamic acid at 4%, aimed at post-acne marks and sun-related pigmentation. Use in the evening and follow with sunscreen the next morning.',
                'price' => 1450, 'compare_price' => 1750, 'stock' => 0,
                'is_featured' => true,
                'variants' => [
                    ['name' => '15 ml', 'price' => 950,  'stock' => 12],
                    ['name' => '30 ml', 'price' => 1450, 'compare_price' => 1750, 'stock' => 8],
                ],
            ],
            [
                'name' => 'Anua Azelaic Acid 10% + Hyaluron Serum',
                'slug' => 'anua-azelaic-acid-10-hyaluron-serum',
                'sku' => 'ANU-AZE-01',
                'brand' => 'anua', 'category' => 'serums',
                'origin' => 'South Korea', 'size_label' => '15 ml / 30 ml',
                'short_description' => 'Calms active breakouts while hydrating.',
                'description' => 'Azelaic acid at 10% for active spots and redness, buffered with hyaluronic acid so it does not strip the barrier. Suits skin that reacts badly to stronger acids.',
                'price' => 1550, 'stock' => 0,
                'is_featured' => true,
                'variants' => [
                    ['name' => '15 ml', 'price' => 1050, 'stock' => 6],
                    ['name' => '30 ml', 'price' => 1550, 'stock' => 4],
                ],
            ],
            [
                'name' => 'AXIS-Y Dark Spot Correcting Glow Serum',
                'slug' => 'axis-y-dark-spot-correcting-glow-serum',
                'sku' => 'AXY-DSC-01',
                'brand' => 'axis-y', 'category' => 'serums',
                'origin' => 'South Korea', 'size_label' => '50 ml',
                'short_description' => 'Squalane and niacinamide for stubborn pigmentation.',
                'description' => 'A lightweight serum built around squalane, niacinamide and papaya extract. Works slowly, so give it eight weeks before judging results.',
                'price' => 1690, 'stock' => 15,
                'is_featured' => true,
            ],
            [
                'name' => 'SKIN1004 Centella Ampoule Serum',
                'slug' => 'skin1004-centella-ampoule-serum',
                'sku' => 'SK1-CEN-SER',
                'brand' => 'skin1004', 'category' => 'serums',
                'origin' => 'South Korea', 'size_label' => '55 ml / 100 ml',
                'short_description' => 'Single-ingredient centella for irritated skin.',
                'description' => 'Madagascar centella asiatica extract with nothing else competing for attention. The one to reach for when your skin is reacting and you want to strip the routine back.',
                'price' => 1350, 'stock' => 0,
                'variants' => [
                    ['name' => '55 ml',  'price' => 1350, 'stock' => 10],
                    ['name' => '100 ml', 'price' => 2100, 'stock' => 5],
                ],
            ],
            [
                'name' => 'SKIN1004 Centella Air-Fit Sunscreen SPF50+',
                'slug' => 'skin1004-centella-air-fit-sunscreen-spf50',
                'sku' => 'SK1-CEN-SPF',
                'brand' => 'skin1004', 'category' => 'sunscreen',
                'origin' => 'South Korea', 'size_label' => '50 ml',
                'short_description' => 'SPF50+ PA++++ with no white cast.',
                'description' => 'Light chemical sunscreen that sits well under makeup and does not pill. Reapply every three hours if you are outdoors.',
                'price' => 1250, 'stock' => 22,
                'is_featured' => true,
            ],
            [
                'name' => 'DR.ALTHEA 345 Relief Cream',
                'slug' => 'dr-althea-345-relief-cream',
                'sku' => 'DRA-345-01',
                'brand' => 'dr-althea', 'category' => 'moisturisers',
                'origin' => 'South Korea', 'size_label' => '15 ml',
                'short_description' => 'Barrier repair cream for compromised skin.',
                'description' => 'Peptide and ceramide cream aimed at a damaged moisture barrier. Small size, so treat it as a targeted treatment rather than an all-over daily moisturiser.',
                'price' => 890, 'stock' => 9,
            ],
            [
                'name' => 'Dabo All-in-One Black Snail Repair Cream',
                'slug' => 'dabo-all-in-one-black-snail-repair-cream',
                'sku' => 'DAB-SNL-01',
                'brand' => 'dabo', 'category' => 'moisturisers',
                'origin' => 'South Korea', 'size_label' => '15 ml',
                'short_description' => 'Snail mucin cream for dryness and marks.',
                'description' => 'Snail secretion filtrate in a thicker cream base. Good overnight in winter or under air conditioning.',
                'price' => 650, 'compare_price' => 720, 'stock' => 14,
            ],
            [
                'name' => 'Dabo Rice Ferment Foam Face Wash',
                'slug' => 'dabo-rice-ferment-foam-face-wash',
                'sku' => 'DAB-RIC-FW',
                'brand' => 'dabo', 'category' => 'cleansers',
                'origin' => 'South Korea', 'size_label' => '100 ml',
                'short_description' => 'Gentle fermented rice cleanser.',
                'description' => 'Low-foam cleanser that leaves skin soft rather than squeaky. Safe for twice-daily use even on dry skin.',
                'price' => 720, 'stock' => 18,
            ],
            [
                'name' => 'Rice Water Bright Face Wash',
                'slug' => 'rice-water-bright-face-wash',
                'sku' => 'AMJ-RIC-FW',
                'brand' => 'amjr-picks', 'category' => 'cleansers',
                'origin' => 'South Korea', 'size_label' => '100 ml / 150 ml',
                'short_description' => 'Daily rice water cleanser for dull skin.',
                'description' => 'Rice water cleanser for everyday use. Rinses clean without leaving a film.',
                'price' => 580, 'stock' => 0,
                'variants' => [
                    ['name' => '100 ml', 'price' => 480, 'stock' => 11],
                    ['name' => '150 ml', 'price' => 580, 'stock' => 9],
                ],
            ],
            [
                'name' => 'Rice Toner',
                'slug' => 'rice-toner',
                'sku' => 'AMJ-RIC-TN',
                'brand' => 'amjr-picks', 'category' => 'toners',
                'origin' => 'South Korea', 'size_label' => '150 ml',
                'short_description' => 'Hydrating rice toner, no alcohol.',
                'description' => 'Apply on damp skin straight after cleansing, then layer your serum on top while it is still tacky.',
                'price' => 690, 'stock' => 16,
            ],
            [
                'name' => 'Rice Cream',
                'slug' => 'rice-cream',
                'sku' => 'AMJ-RIC-CR',
                'brand' => 'amjr-picks', 'category' => 'moisturisers',
                'origin' => 'South Korea', 'size_label' => '50 ml',
                'short_description' => 'Lightweight rice extract moisturiser.',
                'description' => 'A mid-weight cream that suits Dhaka humidity better than heavier occlusive balms.',
                'price' => 750, 'stock' => 4,
            ],
            [
                'name' => 'Glutathione Brightening Serum',
                'slug' => 'glutathione-brightening-serum',
                'sku' => 'AMJ-GLU-01',
                'brand' => 'amjr-picks', 'category' => 'serums',
                'origin' => 'South Korea', 'size_label' => '45 ml',
                'short_description' => 'Glutathione serum for overall tone.',
                'description' => 'Glutathione paired with vitamin C derivatives. Use in the evening and always pair with daily sunscreen.',
                'price' => 1100, 'stock' => 7,
            ],

            // Gadgets
            [
                'name' => '928 Type Self-Defence Flashlight',
                'slug' => '928-type-self-defence-flashlight',
                'sku' => 'GAD-928-FL',
                'brand' => 'amjr-picks', 'category' => 'torches',
                'origin' => 'China', 'size_label' => 'Rechargeable',
                'short_description' => 'High-output torch with a self-defence function.',
                'description' => 'Rechargeable aluminium torch with multiple brightness modes. Sold for personal safety and emergency use.',
                'price' => 1890, 'compare_price' => 2200, 'stock' => 25,
                'is_featured' => true,
            ],
            [
                'name' => '30,000mAh Fast Power Bank',
                'slug' => '30000mah-fast-power-bank',
                'sku' => 'GAD-PWR-30',
                'brand' => 'amjr-picks', 'category' => 'power-banks',
                'origin' => 'China', 'size_label' => '22.5W dual USB-C',
                'short_description' => 'Large capacity power bank with fast charging.',
                'description' => 'Charges a typical phone five to six times. Two USB-C ports plus USB-A, with pass-through charging.',
                'price' => 2450, 'stock' => 12,
            ],
            [
                'name' => 'Type-C Fast Charging Cable',
                'slug' => 'type-c-fast-charging-cable',
                'sku' => 'GAD-CAB-TC',
                'brand' => 'amjr-picks', 'category' => 'cables',
                'origin' => 'China', 'size_label' => '1 m braided',
                'short_description' => '65W braided cable, one metre.',
                'description' => 'Nylon braided USB-C cable rated to 65W. Handles laptop charging as well as phones.',
                'price' => 390, 'stock' => 40,
            ],
            [
                'name' => 'LED Rechargeable Torch',
                'slug' => 'led-rechargeable-torch',
                'sku' => 'GAD-LED-TR',
                'brand' => 'amjr-picks', 'category' => 'torches',
                'origin' => 'China', 'size_label' => '5 modes',
                'short_description' => 'Everyday aluminium torch, USB rechargeable.',
                'description' => 'Five brightness modes including strobe. Useful during load shedding.',
                'price' => 1150, 'stock' => 3,
            ],
        ];
    }
}
