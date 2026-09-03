<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with(['children.products', 'products'])
            ->roots()
            ->orderBy('sort_order')
            ->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create', [
            'category' => new Category(['is_active' => true]),
            'parents'  => Category::roots()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        Category::create($this->validated($request));

        return redirect()->route('admin.categories.index')
            ->with('status', 'Category created.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', [
            'category' => $category,
            'parents'  => Category::roots()->where('id', '!=', $category->id)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $category->update($this->validated($request, $category));

        return redirect()->route('admin.categories.index')
            ->with('status', 'Category updated.');
    }

    public function destroy(Category $category)
    {
        if ($category->children()->exists()) {
            return back()->with('error', 'Move or delete the sub-categories first.');
        }

        if ($category->products()->exists()) {
            return back()->with('error', 'This category still has products in it.');
        }

        $category->delete();

        return back()->with('status', 'Category deleted.');
    }

    private function validated(Request $request, ?Category $category = null): array
    {
        // Empty selects and number inputs arrive as "" — MySQL rejects that
        // in an integer or foreign-key column, so blank them to null first.
        $request->merge([
            'parent_id'  => $request->filled('parent_id') ? $request->parent_id : null,
            'sort_order' => $request->filled('sort_order') ? $request->sort_order : 0,
            'slug'       => $request->filled('slug') ? $request->slug : null,
        ]);

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:120'],
            'slug'        => ['nullable', 'string', 'max:140', Rule::unique('categories')->ignore($category)],
            'parent_id'   => ['nullable', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
