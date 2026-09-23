<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BlogCategoryController extends Controller
{
    public function index()
    {
        return view('admin.blog-categories.index', [
            'categories' => BlogCategory::withCount('posts')->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request)
    {
        BlogCategory::create($this->validated($request));

        return back()->with('status', __('Topic added.'));
    }

    public function update(Request $request, BlogCategory $category)
    {
        $category->update($this->validated($request, $category));

        return back()->with('status', __('Topic updated.'));
    }

    public function destroy(BlogCategory $category)
    {
        if ($category->posts()->exists()) {
            return back()->with('error', __('Posts are filed under this topic. Move them first.'));
        }

        $category->delete();

        return back()->with('status', __('Topic removed.'));
    }

    private function validated(Request $request, ?BlogCategory $category = null): array
    {
        if (! $request->filled('sort_order')) {
            $request->merge(['sort_order' => 0]);
        }

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:80'],
            'slug'        => ['nullable', 'string', 'max:90', Rule::unique('blog_categories')->ignore($category)],
            'description' => ['nullable', 'string', 'max:200'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
