<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryRate extends Model
{
    protected $fillable = ['delivery_tier_id', 'shipping_zone_id', 'rate'];

    protected $casts = ['rate' => 'decimal:2'];

    public function tier()
    {
        return $this->belongsTo(DeliveryTier::class, 'delivery_tier_id');
    }

    public function zone()
    {
        return $this->belongsTo(ShippingZone::class, 'shipping_zone_id');
    }
}
