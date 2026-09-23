<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\Post;
use App\Models\Tag;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    public function __construct(private ImageService $images)
    {
    }

    public function index(Request $request)
    {
        $posts = Post::with(['category', 'author'])
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->category, fn ($q, $id) => $q->where('blog_category_id', $id))
            ->when($request->search, fn ($q, $term) => $q->search($term))
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->paginate(20)
            ->withQueryString();

        return view('admin.posts.index', [
            'posts'      => $posts,
            'categories' => BlogCategory::orderBy('name')->get(),
            'counts'     => Post::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function create()
    {
        return view('admin.posts.create', $this->formData(new Post([
            'status'       => 'draft',
            'is_indexable' => true,
        ])));
    }

    public function store(Request $request)
    {
        $post = DB::transaction(function () use ($request) {
            $data = $this->validated($request);
            $data['user_id'] = auth()->id();

            $post = Post::create($data);
            $post->tags()->sync(Tag::fromList($request->tags));

            return $post;
        });

        return redirect()->route('admin.posts.edit', $post)
            ->with('status', __('Post saved. Work through the SEO checks before publishing.'));
    }

    public function edit(Post $post)
    {
        $post->load('tags');

        return view('admin.posts.edit', $this->formData($post));
    }

    public function update(Request $request, Post $post)
    {
        DB::transaction(function () use ($request, $post) {
            $post->update($this->validated($request, $post));
            $post->tags()->sync(Tag::fromList($request->tags));
        });

        return redirect()->route('admin.posts.index')
            ->with('status', __('Post updated.'));
    }

    public function destroy(Post $post)
    {
        $post->delete();

        return back()->with('status', __('Post deleted.'));
    }

    /* ---------- helpers ---------- */

    private function formData(Post $post): array
    {
        return [
            'post'       => $post,
            'categories' => BlogCategory::orderBy('name')->get(),
            'tagList'    => $post->exists ? $post->tags->pluck('name')->implode(', ') : '',
        ];
    }

    private function validated(Request $request, ?Post $post = null): array
    {
        foreach (['slug', 'blog_category_id', 'published_at'] as $key) {
            if (! $request->filled($key)) {
                $request->merge([$key => null]);
            }
        }

        $reserved = ['category', 'tag', 'search', 'feed'];

        $data = $request->validate([
            'title'            => ['required', 'string', 'max:180'],
            'slug'             => [
                'nullable', 'string', 'max:200', 'regex:/^[a-z0-9-]+$/',
                Rule::unique('posts')->ignore($post),
                Rule::notIn($reserved),
            ],
            'blog_category_id' => ['nullable', 'exists:blog_categories,id'],
            'excerpt'          => ['nullable', 'string', 'max:400'],
            'content'          => ['nullable', 'string', 'max:200000'],
            'cover'            => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            'cover_alt'        => ['nullable', 'string', 'max:160'],

            'status'           => ['required', Rule::in(array_keys(Post::STATUSES))],
            'published_at'     => ['nullable', 'date'],

            'focus_keyword'    => ['nullable', 'string', 'max:120'],
            'meta_title'       => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:200'],
            'og_title'         => ['nullable', 'string', 'max:95'],
            'og_description'   => ['nullable', 'string', 'max:200'],
            'canonical_url'    => ['nullable', 'url', 'max:255'],
            'tags'             => ['nullable', 'string', 'max:300'],
        ], [
            'slug.regex'  => __('Use lowercase letters, numbers and dashes only.'),
            'slug.not_in' => __('That address is used by the blog itself. Pick another.'),
        ]);

        unset($data['tags'], $data['cover']);

        if ($request->hasFile('cover')) {
            if ($post?->cover_image) {
                $this->images->delete($post->cover_image);
            }

            $data['cover_image'] = $this->images->store($request->file('cover'), 'blog');
        }

        $data['is_featured']  = $request->boolean('is_featured');
        $data['is_indexable'] = $request->boolean('is_indexable');

        return $data;
    }
}
