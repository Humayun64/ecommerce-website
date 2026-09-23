<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        // The shop's own number, so the rows arrive with something sensible
        // in them rather than a placeholder you have to hunt down.
        $phone = Setting::get('store_phone', '01347-419040');

        $rows = [
            [
                'code'         => 'cod',
                'name'         => 'Cash on delivery',
                'tagline'      => 'Pay the courier when the parcel arrives.',
                'driver'       => 'cod',
                'needs_sender' => false,
                'needs_txn'    => false,
                'is_default'   => true,
                'sort_order'   => 1,
            ],
            [
                'code'           => 'bkash',
                'name'           => 'bKash',
                'tagline'        => 'Send money, then enter your transaction ID.',
                'driver'         => 'manual',
                'account_number' => $phone,
                'account_type'   => 'Personal',
                'accent'         => '#E2136E',
                'instructions'   => "Open bKash → Send Money\nEnter the number above and the exact order total.\nWhen it goes through you will get a transaction ID — type it below.",
                'sort_order'     => 2,
            ],
            [
                'code'           => 'nagad',
                'name'           => 'Nagad',
                'tagline'        => 'Send money, then enter your transaction ID.',
                'driver'         => 'manual',
                'account_number' => $phone,
                'account_type'   => 'Personal',
                'accent'         => '#EE7622',
                'instructions'   => "Open Nagad → Send Money\nEnter the number above and the exact order total.\nCopy the transaction ID from the confirmation message and type it below.",
                'sort_order'     => 3,
            ],
            [
                'code'           => 'rocket',
                'name'           => 'Rocket',
                'tagline'        => 'Send money, then enter your transaction ID.',
                'driver'         => 'manual',
                'account_number' => $phone,
                'account_type'   => 'Personal',
                'accent'         => '#8C3494',
                'instructions'   => "Dial *322# or open the Rocket app → Send Money\nEnter the number above and the exact order total.\nType the transaction ID from the confirmation message below.",
                'is_active'      => false,
                'sort_order'     => 4,
            ],
            [
                'code'         => 'sslcommerz',
                'name'         => 'Card / online payment',
                'tagline'      => 'Visa, Mastercard and mobile banking in one go.',
                'driver'       => 'gateway',
                'gateway'      => 'sslcommerz',
                'needs_sender' => false,
                'needs_txn'    => false,
                'is_active'    => false,
                'sort_order'   => 5,
            ],
        ];

        foreach ($rows as $row) {
            // updateOrCreate on the code, so running this again after you have
            // edited a number does not undo your edit — it only fills gaps.
            PaymentMethod::firstOrCreate(['code' => $row['code']], $row);
        }
    }
}
