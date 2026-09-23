<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryTier extends Model
{
    protected $fillable = ['name', 'description', 'icon', 'sort_order', 'is_default'];

    protected $casts = ['is_default' => 'boolean'];

    public function rates()
    {
        return $this->hasMany(DeliveryRate::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /** The band a product falls into when nobody has set one. */
    public static function fallback(): ?self
    {
        return static::where('is_default', true)->first()
            ?? static::orderBy('sort_order')->first();
    }

    public function rateFor(int $zoneId): ?float
    {
        $rate = $this->relationLoaded('rates')
            ? $this->rates->firstWhere('shipping_zone_id', $zoneId)
            : $this->rates()->where('shipping_zone_id', $zoneId)->first();

        return $rate ? (float) $rate->rate : null;
    }
}
