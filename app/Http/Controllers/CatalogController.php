<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    /** Everything, /shop, with filters applied from the query string. */
    public function index(Request $request)
    {
        return $this->listing($request, null, null);
    }

    public function category(Request $request, Category $category)
    {
        abort_unless($category->is_active, 404);

        return $this->listing($request, $category, null);
    }

    public function brand(Request $request, Brand $brand)
    {
        abort_unless($brand->is_active, 404);

        return $this->listing($request, null, $brand);
    }

    public function show(Product $product)
    {
        abort_unless($product->is_active, 404);

        $product->load([
            'brand', 'category.parent', 'images',
            'variants.values.attribute',
            'productAttributes.values',
        ]);

        $related = Product::active()
            ->with(['brand', 'primaryImage', 'variants'])
            ->where('id', '!=', $product->id)
            ->when($product->category_id, fn ($q) => $q->where('category_id', $product->category_id))
            ->inRandomOrder()
            ->take(6)
            ->get();

        return view('site.products.show', compact('product', 'related'));
    }

    /* ---------- shared listing ---------- */

    private function listing(Request $request, ?Category $category, ?Brand $brand)
    {
        $query = Product::active()
            ->with(['brand', 'primaryImage', 'variants'])
            ->withMin('variants as min_variant_price', 'price');

        // Category, including everything under a parent.
        if ($category) {
            $ids = $category->children->pluck('id')->push($category->id);
            $query->whereIn('category_id', $ids);
        }

        if ($brand) {
            $query->where('brand_id', $brand->id);
        }

        // Search.
        if ($term = trim((string) $request->q)) {
            $query->where(fn ($q) => $q
                ->where('name', 'like', "%{$term}%")
                ->orWhere('short_description', 'like', "%{$term}%")
                ->orWhere('sku', 'like', "%{$term}%")
                ->orWhereHas('brand', fn ($b) => $b->where('name', 'like', "%{$term}%")));
        }

        // Brand checkboxes.
        if ($brandIds = array_filter((array) $request->brands)) {
            $query->whereIn('brand_id', $brandIds);
        }

        // Attribute values — a product matches if any variant carries the value.
        if ($valueIds = array_filter((array) $request->values)) {
            $query->whereHas('variants.values', fn ($v) => $v->whereIn('attribute_values.id', $valueIds));
        }

        // Price range, checked against variants where they exist.
        foreach (['min' => '>=', 'max' => '<='] as $key => $operator) {
            if (! is_numeric($request->$key)) {
                continue;
            }

            $amount = (float) $request->$key;

            $query->where(fn ($q) => $q
                ->where(fn ($simple) => $simple->where('has_variants', false)->where('price', $operator, $amount))
                ->orWhereHas('variants', fn ($v) => $v->where('price', $operator, $amount)));
        }

        if ($request->boolean('in_stock')) {
            $query->where(fn ($q) => $q
                ->where(fn ($simple) => $simple->where('has_variants', false)->where('stock', '>', 0))
                ->orWhereHas('variants', fn ($v) => $v->where('stock', '>', 0)));
        }

        // Sorting. COALESCE so variant products sort on their cheapest size.
        match ($request->sort) {
            'price_asc'  => $query->orderByRaw('COALESCE(min_variant_price, price) ASC'),
            'price_desc' => $query->orderByRaw('COALESCE(min_variant_price, price) DESC'),
            'name'       => $query->orderBy('name'),
            default      => $query->latest('id'),
        };

        return view('site.products.index', [
            'products'   => $query->paginate(18)->withQueryString(),
            'category'   => $category,
            'brand'      => $brand,
            'allBrands'  => Brand::active()->orderBy('name')->get(),
            'attributes' => Attribute::where('is_filterable', true)
                                ->with('values')->orderBy('sort_order')->get(),
            'heading'    => $category?->name ?? $brand?->name ?? ($request->q ? __('Search results') : __('All products')),
        ]);
    }
}
