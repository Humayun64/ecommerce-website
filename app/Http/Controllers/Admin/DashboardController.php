<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;

class DashboardController extends Controller
{
    public function index()
    {
        $products = Product::with('variants')->get();

        $costed = $products->filter(fn ($p) => $p->margin_percent !== null);

        return view('admin.dashboard', [
            'productCount'  => $products->count(),
            'activeCount'   => $products->where('is_active', true)->count(),
            'categoryCount' => Category::count(),
            'brandCount'    => Brand::count(),

            'lowStock'      => $products->filter(fn ($p) => $p->is_low_stock)->take(8),
            'outOfStock'    => $products->filter(fn ($p) => $p->is_out_of_stock)->count(),

            'stockValue'    => $products->sum(fn ($p) => $p->stock_value),
            'missingCost'   => $products->count() - $costed->count(),
            'avgMargin'     => $costed->count() ? (int) round($costed->avg('margin_percent')) : null,
            'thinMargin'    => $costed->filter(fn ($p) => $p->margin_percent < 15)
                                      ->sortBy('margin_percent')->take(6),

            'recent'        => Product::with('brand')->latest()->take(6)->get(),
        ]);
    }
}
