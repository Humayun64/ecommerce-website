<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class CmsSeeder extends Seeder
{
    public function run(): void
    {
        if (MenuItem::where('location', 'header')->doesntExist()) {
            $this->seedHeader();
        }

        if (MenuItem::where('location', 'footer')->doesntExist()) {
            $this->seedFooter();
        }

        Setting::put([
            'footer_col1_title' => Setting::get('footer_col1_title', 'Shop'),
            'footer_col2_title' => Setting::get('footer_col2_title', 'Your order'),
        ]);
    }

    /** Rebuilds the nav that used to be generated from categories. */
    private function seedHeader(): void
    {
        $order = 1;

        MenuItem::create([
            'location' => 'header', 'label' => 'Home', 'url' => '/', 'sort_order' => $order++,
        ]);

        MenuItem::create([
            'location' => 'header', 'label' => 'All products', 'url' => '/shop', 'sort_order' => $order++,
        ]);

        $children = Category::whereNotNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        foreach ($children as $category) {
            MenuItem::create([
                'location'   => 'header',
                'label'      => $category->name,
                'url'        => '/category/' . $category->slug,
                'sort_order' => $order++,
            ]);
        }

        if ($whatsapp = Setting::get('store_whatsapp')) {
            MenuItem::create([
                'location'   => 'header',
                'label'      => 'Order on WhatsApp',
                'url'        => 'https://wa.me/' . $whatsapp,
                'new_tab'    => true,
                'sort_order' => $order++,
            ]);
        }
    }

    private function seedFooter(): void
    {
        $columnOne = Category::whereNotNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->take(6)
            ->get();

        $order = 1;

        foreach ($columnOne as $category) {
            MenuItem::create([
                'location' => 'footer', 'column' => 1,
                'label' => $category->name, 'url' => '/category/' . $category->slug,
                'sort_order' => $order++,
            ]);
        }

        $order = 1;

        foreach ([
            ['Track a parcel', '/track'],
            ['Delivery charges', '/shipping-policy'],
            ['Returns and refunds', '/return-policy'],
            ['About us', '/about-us'],
            ['Contact us', '/contact'],
        ] as [$label, $url]) {
            MenuItem::create([
                'location' => 'footer', 'column' => 2,
                'label' => $label, 'url' => $url, 'sort_order' => $order++,
            ]);
        }
    }
}
