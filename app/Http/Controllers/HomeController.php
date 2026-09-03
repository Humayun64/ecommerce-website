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
            'departments' => Category::active()->roots()
                                ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
                                ->orderBy('sort_order')->get(),
            'brands'      => Brand::active()->orderBy('sort_order')->take(6)->get(),
        ]);
    }
}
