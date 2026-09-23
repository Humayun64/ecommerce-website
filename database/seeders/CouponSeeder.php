<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

/**
 * Three sample coupons so the feature can be tested immediately.
 * Delete them from the admin panel once you have made your own.
 */
class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $samples = [
            [
                'code'                     => 'WELCOME10',
                'description'              => 'Sample — 10% off a first order, capped at ৳300',
                'type'                     => 'percent',
                'value'                    => 10,
                'max_discount'             => 300,
                'min_spend'                => 1000,
                'first_order_only'         => true,
                'usage_limit_per_customer' => 1,
            ],
            [
                'code'        => 'FREESHIP',
                'description' => 'Sample — free delivery over ৳1,200',
                'type'        => 'fixed',
                'value'       => 0,
                'min_spend'   => 1200,
                'free_shipping' => true,
            ],
            [
                'code'        => 'FLAT200',
                'description' => 'Sample — ৳200 off orders over ৳1,500',
                'type'        => 'fixed',
                'value'       => 200,
                'min_spend'   => 1500,
                'usage_limit' => 50,
            ],
        ];

        foreach ($samples as $sample) {
            Coupon::firstOrCreate(['code' => $sample['code']], $sample);
        }
    }
}
