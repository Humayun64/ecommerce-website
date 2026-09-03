<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AttributeValue extends Model
{
    protected $fillable = ['attribute_id', 'value', 'slug', 'sort_order'];

    protected static function booted(): void
    {
        static::saving(function (AttributeValue $value) {
            if (blank($value->slug)) {
                $value->slug = Str::slug($value->value);
            }
        });
    }

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

    public function variants()
    {
        return $this->belongsToMany(ProductVariant::class, 'attribute_value_product_variant');
    }
}
