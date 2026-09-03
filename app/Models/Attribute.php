<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Attribute extends Model
{
    protected $fillable = ['name', 'slug', 'sort_order', 'is_filterable'];

    protected $casts = ['is_filterable' => 'boolean'];

    protected static function booted(): void
    {
        static::saving(function (Attribute $attribute) {
            if (blank($attribute->slug)) {
                $attribute->slug = Str::slug($attribute->name);
            }
        });
    }

    public function values()
    {
        return $this->hasMany(AttributeValue::class)->orderBy('sort_order');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_attribute')
            ->withPivot('sort_order');
    }
}
