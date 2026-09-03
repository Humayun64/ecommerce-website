<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::withCount('products')->orderBy('sort_order')->get();

        return view('admin.brands.index', compact('brands'));
    }

    public function create()
    {
        return view('admin.brands.create', [
            'brand' => new Brand(['is_active' => true]),
        ]);
    }

    public function store(Request $request)
    {
        Brand::create($this->validated($request));

        return redirect()->route('admin.brands.index')
            ->with('status', 'Brand created.');
    }

    public function edit(Brand $brand)
    {
        return view('admin.brands.edit', compact('brand'));
    }

    public function update(Request $request, Brand $brand)
    {
        $brand->update($this->validated($request, $brand));

        return redirect()->route('admin.brands.index')
            ->with('status', 'Brand updated.');
    }

    public function destroy(Brand $brand)
    {
        if ($brand->products()->exists()) {
            return back()->with('error', 'This brand still has products assigned to it.');
        }

        $brand->delete();

        return back()->with('status', 'Brand deleted.');
    }

    private function validated(Request $request, ?Brand $brand = null): array
    {
        // Empty number inputs arrive as "" — MySQL rejects that in an
        // integer column, so normalise before validating.
        $request->merge([
            'sort_order' => $request->filled('sort_order') ? $request->sort_order : 0,
            'slug'       => $request->filled('slug') ? $request->slug : null,
        ]);

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:120'],
            'slug'        => ['nullable', 'string', 'max:140', Rule::unique('brands')->ignore($brand)],
            'country'     => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
