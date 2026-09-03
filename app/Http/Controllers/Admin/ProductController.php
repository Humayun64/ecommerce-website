<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function __construct(private ImageService $images)
    {
    }

    public function index(Request $request)
    {
        $products = Product::with(['brand', 'category', 'variants', 'primaryImage'])
            ->when($request->search, fn ($q, $term) => $q->where(fn ($sub) =>
                $sub->where('name', 'like', "%{$term}%")->orWhere('sku', 'like', "%{$term}%")))
            ->when($request->brand, fn ($q, $id) => $q->where('brand_id', $id))
            ->when($request->category, fn ($q, $id) => $q->where('category_id', $id))
            ->when($request->status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($request->status === 'low', fn ($q) => $q->whereColumn('stock', '<=', 'low_stock_threshold'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.products.index', [
            'products'   => $products,
            'brands'     => Brand::orderBy('name')->get(),
            'categories' => Category::with('parent')->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.products.create', $this->formData(new Product([
            'type'                => 'physical',
            'origin'              => 'South Korea',
            'is_active'           => true,
            'is_indexable'        => true,
            'low_stock_threshold' => 5,
        ])));
    }

    public function store(ProductRequest $request)
    {
        $product = DB::transaction(function () use ($request) {
            $product = Product::create($this->attributesFor($request));

            $this->syncAxes($product, $request->input('attribute_ids', []));
            $this->syncVariants($product, $request->input('variants', []));
            $this->storeImages($product, $request);

            return $product;
        });

        return redirect()->route('admin.products.edit', $product)
            ->with('status', 'Product created. Add images and check the SEO panel before it goes live.');
    }

    public function edit(Product $product)
    {
        $product->load(['variants.values.attribute', 'images', 'productAttributes']);

        return view('admin.products.edit', $this->formData($product));
    }

    public function update(ProductRequest $request, Product $product)
    {
        DB::transaction(function () use ($request, $product) {
            $product->update($this->attributesFor($request));

            $this->syncAxes($product, $request->input('attribute_ids', []));
            $this->syncVariants($product, $request->input('variants', []));
            $this->storeImages($product, $request);
        });

        return redirect()->route('admin.products.index')
            ->with('status', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('status', 'Product deleted. It is hidden from the storefront but the record is kept for old orders.');
    }

    /* ---------- images ---------- */

    public function deleteImage(Product $product, ProductImage $image)
    {
        abort_unless($image->product_id === $product->id, 404);

        $wasPrimary = $image->is_primary;

        $this->images->delete($image->path);
        $image->delete();

        if ($wasPrimary) {
            $product->images()->first()?->update(['is_primary' => true]);
        }

        return back()->with('status', 'Image removed.');
    }

    public function makePrimaryImage(Product $product, ProductImage $image)
    {
        abort_unless($image->product_id === $product->id, 404);

        $product->images()->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);

        return back()->with('status', 'Main image changed.');
    }

    /* ---------- helpers ---------- */

    private function formData(Product $product): array
    {
        return [
            'product'       => $product,
            'brands'        => Brand::orderBy('name')->get(),
            'categories'    => Category::with('parent')->orderBy('name')->get()
                                   ->sortBy(fn ($c) => $c->full_name),
            'allAttributes' => Attribute::with('values')->orderBy('sort_order')->get(),
        ];
    }

    private function attributesFor(ProductRequest $request): array
    {
        $data = $request->safe()->except(['variants', 'images', 'attribute_ids']);

        $variants = collect($request->input('variants', []))
            ->filter(fn ($v) => filled($v['price'] ?? null) || filled($v['name'] ?? null));

        $data['has_variants'] = $variants->isNotEmpty();
        $data['is_featured']  = $request->boolean('is_featured');
        $data['is_active']    = $request->boolean('is_active');
        $data['is_indexable'] = $request->boolean('is_indexable');

        if ($data['has_variants']) {
            $data['stock'] = 0;
        }

        return $data;
    }

    /** Which attributes this product varies on. Capped at two. */
    private function syncAxes(Product $product, array $ids): void
    {
        $payload = [];
        $order   = 1;

        foreach (array_slice(array_filter($ids), 0, 2) as $id) {
            $payload[$id] = ['sort_order' => $order++];
        }

        $product->productAttributes()->sync($payload);
    }

    private function syncVariants(Product $product, array $rows): void
    {
        $keep  = [];
        $order = 1;

        foreach ($rows as $row) {
            $valueIds = array_filter($row['value_ids'] ?? []);
            $label    = trim($row['name'] ?? '');

            // A row with neither a label nor any values is an empty line.
            if (! $valueIds && $label === '') {
                continue;
            }

            $payload = [
                'name'          => $label ?: 'Variation ' . $order,
                'sku'           => filled($row['sku'] ?? null)
                                    ? $row['sku']
                                    : $product->sku . '-' . $order,
                'price'         => $row['price'] ?? 0,
                'compare_price' => filled($row['compare_price'] ?? null) ? $row['compare_price'] : null,
                'cost_price'    => filled($row['cost_price'] ?? null) ? $row['cost_price'] : null,
                'stock'         => $row['stock'] ?? 0,
                'sort_order'    => $order++,
            ];

            if (filled($row['id'] ?? null) && $variant = $product->variants()->find($row['id'])) {
                $variant->update($payload);
            } else {
                $variant = $product->variants()->create($payload);
            }

            if ($valueIds) {
                $variant->values()->sync($valueIds);
                $variant->load('values.attribute');

                // Rebuild the label from the values so it can never drift.
                $variant->update(['name' => $variant->buildName()]);
            }

            $keep[] = $variant->id;
        }

        $product->variants()->whereNotIn('id', $keep ?: [0])->delete();
    }

    private function storeImages(Product $product, ProductRequest $request): void
    {
        if (! $request->hasFile('images')) {
            return;
        }

        $hasPrimary = $product->images()->where('is_primary', true)->exists();
        $sort       = (int) $product->images()->max('sort_order');

        foreach ($request->file('images') as $file) {
            $product->images()->create([
                'path'       => $this->images->store($file),
                'alt'        => $product->name,
                'is_primary' => ! $hasPrimary,
                'sort_order' => ++$sort,
            ]);

            $hasPrimary = true;
        }
    }
}
