<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    public function run(): void
    {
        // Only fills what is empty, so running this again never overwrites
        // wording you have since changed.
        $defaults = [
            'contact_heading'      => 'Talk to us',
            'contact_intro'        => 'A message here reaches the same people who pack the parcels. We read every one, and we answer every one.',
            'contact_reply_time'   => 'We usually reply the same day, and always within one working day.',
            'contact_hours'        => "Saturday to Thursday — 10:00am to 8:00pm\nFriday — 3:00pm to 8:00pm",
            'contact_topics'       => "Question about an order\nIs this product genuine?\nDelivery or payment\nReturn or refund\nWholesale or reselling\nSomething else",
            'contact_form_enabled' => '1',
        ];

        $fill = [];

        foreach ($defaults as $key => $value) {
            if (trim((string) Setting::get($key, '')) === '') {
                $fill[$key] = $value;
            }
        }

        if ($fill) {
            Setting::put($fill);
        }

        $this->addToMenu('header', 'Contact', 90);
        $this->addToMenu('footer', 'Contact us', 90);
    }

    private function addToMenu(string $location, string $label, int $sort): void
    {
        $exists = MenuItem::where('location', $location)
            ->where('url', '/contact')
            ->exists();

        if ($exists) {
            return;
        }

        MenuItem::create([
            'location'   => $location,
            'column'     => 1,
            'label'      => $label,
            'url'        => '/contact',
            'new_tab'    => false,
            'is_active'  => true,
            'sort_order' => $sort,
        ]);
    }
}
