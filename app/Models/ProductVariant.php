<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'name', 'sku', 'price',
        'compare_price', 'cost_price', 'stock', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'price'         => 'decimal:2',
        'compare_price' => 'decimal:2',
        'cost_price'    => 'decimal:2',
        'is_active'     => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function values()
    {
        return $this->belongsToMany(AttributeValue::class, 'attribute_value_product_variant');
    }

    public function getUnitProfitAttribute(): ?float
    {
        return $this->cost_price === null
            ? null
            : (float) $this->price - (float) $this->cost_price;
    }

    /**
     * "Shade 21 / 30 ml" — rebuilt from the linked values so the label
     * can never drift out of sync with what the variation actually is.
     */
    public function buildName(): string
    {
        $parts = $this->values->sortBy(fn ($v) => $v->attribute->sort_order)
            ->pluck('value')
            ->all();

        return $parts ? implode(' / ', $parts) : $this->name;
    }

    /** Sorted list of value ids — the fingerprint of this combination. */
    public function valueKey(): string
    {
        return $this->values->pluck('id')->sort()->implode('-');
    }
}
