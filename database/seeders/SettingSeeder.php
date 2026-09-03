<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // store
            'store_name'        => 'AMJR Global',
            'store_tagline'     => 'Connecting business, creating value',
            'store_email'       => 'akmolhosen2006@gmail.com',
            'store_phone'       => '01347-419040',
            'store_whatsapp'    => '8801347419040',
            'store_address'     => 'Uttara, Dhaka, Bangladesh',

            // messaging
            'topbar_message'    => 'Cash on delivery across Bangladesh — pay when the parcel reaches you',
            'hero_eyebrow'      => 'Sourced directly from Korea',
            'hero_heading'      => 'Real formulas, not lookalikes.',
            'hero_text'         => 'Every bottle is imported in our own name and arrives sealed with its batch code intact. Check it against the brand before you pay a taka.',
            'footer_about'      => 'We import Korean skincare and everyday gadgets into Bangladesh and sell them without a middleman markup.',

            // delivery promises shown on the storefront
            'delivery_dhaka'    => '60',
            'delivery_suburb'   => '90',
            'delivery_city'     => '120',
            'delivery_outside'  => '150',
            'free_delivery_over' => '2000',
            'return_days'       => '7',

            // social
            'facebook_url'      => 'https://www.facebook.com/',
            'instagram_url'     => '',
            'youtube_url'       => '',

            // seo
            'meta_title'        => 'AMJR Global — Authentic Korean skincare & gadgets in Bangladesh',
            'meta_description'  => 'Imported Korean skincare and everyday gadgets, delivered across Bangladesh with cash on delivery. Every product sealed with its batch code intact.',
        ];

        foreach ($defaults as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }

        Setting::flush();
    }
}
