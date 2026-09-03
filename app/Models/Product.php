<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id', 'brand_id', 'name', 'slug', 'sku', 'type', 'origin',
        'size_label', 'short_description', 'description',
        'price', 'compare_price', 'cost_price',
        'stock', 'low_stock_threshold', 'has_variants',
        'is_featured', 'is_active',
        'meta_title', 'meta_description',
        'og_title', 'og_description', 'og_image',
        'canonical_url', 'is_indexable',
    ];

    protected $casts = [
        'price'         => 'decimal:2',
        'compare_price' => 'decimal:2',
        'cost_price'    => 'decimal:2',
        'has_variants'  => 'boolean',
        'is_featured'   => 'boolean',
        'is_active'     => 'boolean',
        'is_indexable'  => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            if (blank($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /** Which axes this product varies on. Named to avoid clashing with Eloquent's own attributes. */
    public function productAttributes()
    {
        return $this->belongsToMany(Attribute::class, 'product_attribute')
            ->withPivot('sort_order')
            ->orderBy('product_attribute.sort_order');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /* ---------- stock ---------- */

    public function getTotalStockAttribute(): int
    {
        return $this->has_variants
            ? (int) $this->variants->sum('stock')
            : (int) $this->stock;
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->total_stock > 0
            && $this->total_stock <= $this->low_stock_threshold;
    }

    public function getIsOutOfStockAttribute(): bool
    {
        return $this->total_stock <= 0;
    }

    /* ---------- pricing ---------- */

    /** Lowest variant price, for "from ৳X" on listings. */
    public function getDisplayPriceAttribute()
    {
        return $this->has_variants && $this->variants->count()
            ? $this->variants->min('price')
            : $this->price;
    }

    public function getDiscountPercentAttribute(): ?int
    {
        if (! $this->compare_price || $this->compare_price <= $this->price) {
            return null;
        }

        return (int) round(100 - ($this->price / $this->compare_price * 100));
    }

    /** Profit on one unit at the current selling price. */
    public function getUnitProfitAttribute(): ?float
    {
        if ($this->has_variants) {
            $variant = $this->variants->first(fn ($v) => $v->cost_price !== null);

            return $variant ? (float) $variant->price - (float) $variant->cost_price : null;
        }

        if ($this->cost_price === null) {
            return null;
        }

        return (float) $this->price - (float) $this->cost_price;
    }

    /** Margin as a percentage of the selling price. */
    public function getMarginPercentAttribute(): ?int
    {
        $profit = $this->unit_profit;
        $price  = (float) $this->display_price;

        if ($profit === null || $price <= 0) {
            return null;
        }

        return (int) round($profit / $price * 100);
    }

    /** Money currently tied up in this product's stock. */
    public function getStockValueAttribute(): float
    {
        if ($this->has_variants) {
            return (float) $this->variants->sum(fn ($v) => (float) $v->cost_price * $v->stock);
        }

        return (float) $this->cost_price * $this->stock;
    }

    /* ---------- seo ---------- */

    public function getSeoTitleAttribute(): string
    {
        return $this->meta_title ?: $this->name;
    }

    public function getSeoDescriptionAttribute(): string
    {
        return $this->meta_description ?: Str::limit(strip_tags($this->short_description ?? ''), 155);
    }

    public function getShareTitleAttribute(): string
    {
        return $this->og_title ?: $this->seo_title;
    }

    public function getShareDescriptionAttribute(): string
    {
        return $this->og_description ?: $this->seo_description;
    }
}
