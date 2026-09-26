<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * The map Google reads the shop from.
 *
 * Only pages a search engine should actually index go in: products and posts
 * that are live, and only the ones not marked no-index. A sitemap listing a
 * page that says "do not index me" is a contradiction Search Console will
 * report back as an error.
 */
class SitemapController extends Controller
{
    public function index()
    {
        $xml = Cache::remember('sitemap.xml', now()->addHours(6), fn () => $this->build());

        return response($xml, 200, [
            'Content-Type'  => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=21600',
        ]);
    }

    public function robots()
    {
        $lines = [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /account',
            'Disallow: /cart',
            'Disallow: /checkout',
            'Disallow: /returns',
            'Disallow: /track',
            '',
            'Sitemap: ' . route('sitemap'),
            '',
        ];

        return response(implode("\n", $lines), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    private function build(): string
    {
        $urls = [];

        $urls[] = $this->url(route('home'), null, 'daily', '1.0');
        $urls[] = $this->url(route('shop'), null, 'daily', '0.9');

        if (Schema::hasTable('products')) {
            $products = Product::where('is_active', true)
                ->when(Schema::hasColumn('products', 'is_indexable'), fn ($q) => $q->where('is_indexable', true))
                ->orderBy('id')
                ->get(['slug', 'updated_at']);

            foreach ($products as $product) {
                $urls[] = $this->url(route('shop.product', $product->slug), $product->updated_at, 'weekly', '0.8');
            }
        }

        if (Schema::hasTable('categories')) {
            $categories = Category::when(
                Schema::hasColumn('categories', 'is_active'),
                fn ($q) => $q->where('is_active', true)
            )->orderBy('id')->get(['slug', 'updated_at']);

            foreach ($categories as $category) {
                $urls[] = $this->url(route('shop', ['category' => $category->slug]), $category->updated_at, 'weekly', '0.7');
            }
        }

        if (Schema::hasTable('posts')) {
            $urls[] = $this->url(route('blog.index'), null, 'weekly', '0.7');

            $posts = Post::live()
                ->when(Schema::hasColumn('posts', 'is_indexable'), fn ($q) => $q->where('is_indexable', true))
                ->orderBy('id')
                ->get(['slug', 'updated_at']);

            foreach ($posts as $post) {
                $urls[] = $this->url(route('blog.show', $post->slug), $post->updated_at, 'monthly', '0.6');
            }
        }

        if (Schema::hasTable('pages')) {
            $pages = Page::when(
                Schema::hasColumn('pages', 'is_active'),
                fn ($q) => $q->where('is_active', true)
            )->orderBy('id')->get(['slug', 'updated_at']);

            foreach ($pages as $page) {
                $urls[] = $this->url(url('/' . $page->slug), $page->updated_at, 'monthly', '0.5');
            }
        }

        if (\Illuminate\Support\Facades\Route::has('contact')) {
            $urls[] = $this->url(route('contact'), null, 'monthly', '0.5');
        }

        return '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n"
            . implode("\n", $urls) . "\n"
            . '</urlset>';
    }

    private function url(string $loc, $updated, string $frequency, string $priority): string
    {
        $out = '  <url><loc>' . htmlspecialchars($loc, ENT_XML1) . '</loc>';

        if ($updated) {
            $out .= '<lastmod>' . $updated->toAtomString() . '</lastmod>';
        }

        return $out . "<changefreq>{$frequency}</changefreq><priority>{$priority}</priority></url>";
    }
}
