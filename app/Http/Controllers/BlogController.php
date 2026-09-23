<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::live()->with(['category', 'author'])
            ->search($request->q)
            ->when($request->category, fn ($q, $slug) =>
                $q->whereHas('category', fn ($c) => $c->where('slug', $slug)))
            ->when($request->tag, fn ($q, $slug) =>
                $q->whereHas('tags', fn ($t) => $t->where('slug', $slug)))
            ->latest('published_at');

        // The featured post only headlines an unfiltered first page.
        $featured = null;

        if (! $request->hasAny(['q', 'category', 'tag', 'page'])) {
            $featured = Post::live()->with('category')->where('is_featured', true)
                ->latest('published_at')->first();

            if ($featured) {
                $query->where('id', '!=', $featured->id);
            }
        }

        return view('site.blog.index', [
            'posts'      => $query->paginate(9)->withQueryString(),
            'featured'   => $featured,
            'categories' => BlogCategory::active()->withCount(['posts' => fn ($q) => $q->live()])
                                ->orderBy('sort_order')->get(),
            'tags'       => Tag::whereHas('posts', fn ($q) => $q->live())->orderBy('name')->take(20)->get(),
            'activeCategory' => $request->category,
            'activeTag'      => $request->tag,
        ]);
    }

    public function show(string $slug)
    {
        $post = Post::with(['category', 'author', 'tags'])->where('slug', $slug)->firstOrFail();

        // Drafts and scheduled posts are readable by admins so they can
        // preview exactly what will go live.
        if (! $post->is_live) {
            abort_unless(auth()->check() && auth()->user()->is_admin, 404);
        }

        $post->increment('view_count');

        $related = Post::live()
            ->where('id', '!=', $post->id)
            ->when($post->blog_category_id, fn ($q) => $q->where('blog_category_id', $post->blog_category_id))
            ->latest('published_at')
            ->take(3)
            ->get();

        if ($related->count() < 3) {
            $related = $related->merge(
                Post::live()->where('id', '!=', $post->id)
                    ->whereNotIn('id', $related->pluck('id'))
                    ->latest('published_at')->take(3 - $related->count())->get()
            );
        }

        return view('site.blog.show', compact('post', 'related'));
    }
}
