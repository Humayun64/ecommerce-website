<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        $base = Product::active()
            ->with(['brand', 'primaryImage', 'variants']);

        $featured    = (clone $base)->featured()->take(12)->get();
        $departments = $this->departments();

        // A row of products for each department that has any, so the home
        // page shows the whole range and not only the best sellers.
        $shelves = $departments
            ->filter(fn ($department) => $department->products_count > 0)
            ->take(6)
            ->map(fn ($department) => [
                'department' => $department,
                'products'   => (clone $base)->whereIn('category_id', $department->tree_ids)->latest('id')->take(6)->get(),
            ])
            ->filter(fn ($shelf) => $shelf['products']->isNotEmpty())
            ->values();

        // "This week's pick" is chosen on a product's edit page. If nothing
        // is chosen, or that product is now a draft, the first featured
        // product stands in.
        $pickId = (int) \App\Models\Setting::get('home_pick_product_id', 0);
        $pick   = $pickId ? (clone $base)->whereKey($pickId)->first() : null;

        return view('site.home', [
            'featured'    => $featured,
            'pick'        => $pick ?? $featured->first(),
            'pickLabel'   => \App\Models\Setting::get('home_pick_label') ?: __("This week's pick"),
            'newest'      => (clone $base)->latest('id')->take(12)->get(),
            'departments' => $departments,
            'shelves'     => $shelves,
            'brands'      => Brand::active()->orderBy('sort_order')->take(6)->get(),
        ]);
    }

    /**
     * The top-level categories, each with a count of everything a shopper
     * would find inside it.
     *
     * Products usually sit in a sub-category (Skincare > Serums), so
     * counting only the ones attached directly to "Skincare" gave a tile
     * that said 01 or 00 on a full department. The count here walks down
     * through every level beneath the department.
     */
    private function departments()
    {
        $roots    = Category::active()->roots()->orderBy('sort_order')->get();
        $children = Category::active()->whereNotNull('parent_id')->get(['id', 'parent_id'])->groupBy('parent_id');

        $perCategory = Product::active()
            ->whereNotNull('category_id')
            ->selectRaw('category_id, COUNT(*) as total')
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        foreach ($roots as $root) {
            $count = 0;
            $queue = [$root->id];
            $seen  = [];

            while ($queue) {
                $id = array_shift($queue);

                if (isset($seen[$id])) {
                    continue;   // guards against a category saved as its own ancestor
                }

                $seen[$id] = true;
                $count    += (int) ($perCategory[$id] ?? 0);

                foreach ($children[$id] ?? [] as $child) {
                    $queue[] = $child->id;
                }
            }

            $root->products_count = $count;
            $root->tree_ids       = array_keys($seen);   // the department and everything under it
        }

        return $roots;
    }
}
