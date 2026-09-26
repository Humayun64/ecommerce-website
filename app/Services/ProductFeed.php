<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Str;

/**
 * The product feed Facebook and Google read to build a catalog.
 *
 * Both want RSS 2.0 with Google's `g:` namespace — the same document serves
 * a Facebook Product Catalog and a Google Merchant Center feed, which is why
 * there is one builder here rather than two.
 */
class ProductFeed
{
    public function enabled(string $channel): bool
    {
        return (string) Setting::get("feed_{$channel}_enabled", '0') === '1';
    }

    /** Only things a customer could actually buy belong in an ad. */
    public function products()
    {
        return Product::with(['brand', 'category', 'primaryImage', 'images'])
            ->where('is_active', true)
            ->where('price', '>', 0)
            ->orderBy('id')
            ->get();
    }

    public function xml(string $channel): string
    {
        $store = Setting::get('store_name', 'AMJR Global');
        $desc  = Setting::get('store_tagline', 'Korean skincare and gadgets');

        $items = [];

        foreach ($this->products() as $product) {
            $items[] = $this->item($product, $channel);
        }

        $head = '<?xml version="1.0" encoding="UTF-8"?>';
        $open = '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0"><channel>';
        $meta = $this->tag('title', $store)
              . $this->tag('link', url('/'))
              . $this->tag('description', $desc);

        return $head . "\n" . $open . "\n" . $meta . "\n" . implode("\n", $items) . "\n</channel></rss>";
    }

    private function item(Product $product, string $channel): string
    {
        $out = '<item>'
            . $this->tag('g:id', (string) ($product->sku ?: $product->id))
            . $this->tag('g:title', Str::limit($product->name, 145, ''))
            . $this->tag('g:description', $this->description($product))
            . $this->tag('g:link', route('shop.product', $product->slug))
            . $this->tag('g:condition', 'new')
            . $this->tag('g:availability', $this->availability($product))
            . $this->prices($product);

        $primary = $product->primaryImage ?: $product->images->first();

        if ($primary) {
            $out .= $this->tag('g:image_link', url($primary->url));

            foreach ($product->images as $extra) {
                if ($extra->id === $primary->id) {
                    continue;
                }

                $out .= $this->tag('g:additional_image_link', url($extra->url));
            }
        }

        if ($product->brand?->name) {
            $out .= $this->tag('g:brand', $product->brand->name);
        }

        if ($product->category?->name) {
            $out .= $this->tag('g:product_type', $product->category->name);
        }

        // Neither platform will take an item with no barcode unless you say
        // outright that it does not have one.
        $out .= $this->tag('g:identifier_exists', 'no');

        if ($channel === 'facebook') {
            $out .= $this->tag('g:inventory', (string) max(0, (int) $product->stock));
        }

        return $out . '</item>';
    }

    /**
     * Google reads `price` as the normal price and `sale_price` as what it
     * costs today — the opposite way round from how the shop stores it, so a
     * product on offer has to be turned around here or the ad shows the
     * discount as the full price and no saving at all.
     */
    private function prices(Product $product): string
    {
        $price   = (float) $product->price;
        $compare = (float) $product->compare_price;

        if ($compare > $price) {
            return $this->tag('g:price', $this->money($compare))
                 . $this->tag('g:sale_price', $this->money($price));
        }

        return $this->tag('g:price', $this->money($price));
    }

    private function money(float $amount): string
    {
        return number_format($amount, 2, '.', '') . ' BDT';
    }

    private function description(Product $product): string
    {
        $text = $product->short_description
            ?: strip_tags((string) $product->description)
            ?: $product->name;

        // The stored description is HTML, so stripping its tags leaves the
        // entities behind. Left encoded they would show in the ad itself as
        // "Snail &amp; Mucin", so they are turned back into real characters —
        // safe here because the value goes inside CDATA.
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return Str::limit(trim(preg_replace('/\s+/', ' ', $text)), 4900, '');
    }

    private function availability(Product $product): string
    {
        if ($product->has_variants) {
            return 'in stock';
        }

        return (int) $product->stock > 0 ? 'in stock' : 'out of stock';
    }

    /** Text goes in a CDATA block so an ampersand in a name cannot break the feed. */
    private function tag(string $name, string $value): string
    {
        $value = str_replace(']]>', ']]&gt;', $value);

        return "<{$name}><![CDATA[{$value}]]></{$name}>";
    }
}
