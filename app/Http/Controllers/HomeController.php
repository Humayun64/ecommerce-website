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

        return view('site.home', [
            'featured'    => (clone $base)->featured()->take(6)->get(),
            'newest'      => (clone $base)->latest('id')->take(6)->get(),
            'departments' => $this->departments(),
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
        }

        return $roots;
    }
}
