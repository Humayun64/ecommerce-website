<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Support\PageTemplates;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::orderBy('sort_order')->orderBy('title')->get();

        // Only offer a quick-create for templates that do not exist yet.
        $existing = $pages->pluck('slug')->all();

        $available = collect(PageTemplates::all())
            ->reject(fn ($label, $slug) => in_array($slug, $existing));

        return view('admin.pages.index', compact('pages', 'available'));
    }

    public function create(Request $request)
    {
        $page = new Page(['is_published' => true, 'is_indexable' => true]);

        // "Quick create" lands here with a template name.
        if ($template = PageTemplates::get((string) $request->template)) {
            $page->fill($template);
        }

        return view('admin.pages.create', compact('page'));
    }

    public function store(Request $request)
    {
        $page = Page::create($this->validated($request));

        return redirect()->route('admin.pages.edit', $page)
            ->with('status', __('Page created. Check it on the site, then add it to a menu.'));
    }

    public function edit(Page $page)
    {
        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page)
    {
        $page->update($this->validated($request, $page));

        return redirect()->route('admin.pages.index')
            ->with('status', __('Page updated.'));
    }

    public function destroy(Page $page)
    {
        $page->delete();

        return back()->with('status', __('Page deleted. Any menu link to it will now 404 — remove it from the menu too.'));
    }

    private function validated(Request $request, ?Page $page = null): array
    {
        if (! $request->filled('slug')) {
            $request->merge(['slug' => null]);
        }

        if (! $request->filled('sort_order')) {
            $request->merge(['sort_order' => 0]);
        }

        $reserved = ['shop', 'cart', 'checkout', 'track', 'account', 'admin', 'login', 'register', 'product', 'category', 'brand', 'dashboard', 'profile', 'coupon', 'order'];

        $data = $request->validate([
            'title'            => ['required', 'string', 'max:160'],
            'slug'             => [
                'nullable', 'string', 'max:180', 'regex:/^[a-z0-9-]+$/',
                Rule::unique('pages')->ignore($page),
                Rule::notIn($reserved),
            ],
            'excerpt'          => ['nullable', 'string', 'max:300'],
            'content'          => ['nullable', 'string', 'max:60000'],
            'meta_title'       => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:180'],
            'sort_order'       => ['nullable', 'integer', 'min:0'],
        ], [
            'slug.regex' => __('Use lowercase letters, numbers and dashes only.'),
            'slug.not_in' => __('That address is already used by the shop itself. Pick another.'),
        ]);

        $data['is_published'] = $request->boolean('is_published');
        $data['is_indexable'] = $request->boolean('is_indexable');

        return $data;
    }
}
