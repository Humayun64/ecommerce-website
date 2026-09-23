<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingZone extends Model
{
    protected $fillable = ['name', 'description', 'rate', 'delivery_time', 'sort_order', 'is_active'];

    protected $casts = ['rate' => 'decimal:2', 'is_active' => 'boolean'];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function rates()
    {
        return $this->hasMany(DeliveryRate::class);
    }

    /** Used when a zone has no rate set for a band yet. */
    public function getBaseRateAttribute(): float
    {
        return (float) $this->rate;
    }
}
