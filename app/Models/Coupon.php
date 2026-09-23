<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'description', 'type', 'value',
        'max_discount', 'min_spend',
        'free_shipping', 'first_order_only',
        'category_id', 'brand_id',
        'usage_limit', 'usage_limit_per_customer', 'used_count',
        'starts_at', 'expires_at', 'is_active',
    ];

    protected $casts = [
        'value'            => 'decimal:2',
        'max_discount'     => 'decimal:2',
        'min_spend'        => 'decimal:2',
        'free_shipping'    => 'boolean',
        'first_order_only' => 'boolean',
        'is_active'        => 'boolean',
        'starts_at'        => 'datetime',
        'expires_at'       => 'datetime',
    ];

    protected static function booted(): void
    {
        // Codes are stored and compared uppercase, so "save10" and "SAVE10"
        // are the same coupon however the customer types it.
        static::saving(function (Coupon $coupon) {
            $coupon->code = Str::upper(trim($coupon->code));
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

    public function usages()
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /* ---------- state ---------- */

    public function getHasStartedAttribute(): bool
    {
        return ! $this->starts_at || $this->starts_at->isPast();
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getIsExhaustedAttribute(): bool
    {
        return $this->usage_limit !== null && $this->used_count >= $this->usage_limit;
    }

    /** What the admin list shows in the status column. */
    public function getStateAttribute(): string
    {
        if (! $this->is_active)    return 'disabled';
        if ($this->is_expired)     return 'expired';
        if ($this->is_exhausted)   return 'used up';
        if (! $this->has_started)  return 'scheduled';

        return 'live';
    }

    public function getValueLabelAttribute(): string
    {
        return $this->type === 'percent'
            ? rtrim(rtrim(number_format((float) $this->value, 2), '0'), '.') . '%'
            : '৳' . number_format((float) $this->value);
    }

    /** "Whole cart", "Serums only", "Anua only". */
    public function getScopeLabelAttribute(): string
    {
        if ($this->category_id) {
            return ($this->category->name ?? 'Category') . ' only';
        }

        if ($this->brand_id) {
            return ($this->brand->name ?? 'Brand') . ' only';
        }

        return 'Whole cart';
    }
}
