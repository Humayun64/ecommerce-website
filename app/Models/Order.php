<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'user_id',
        'customer_name', 'customer_phone', 'customer_email',
        'shipping_address', 'shipping_area', 'shipping_zone_id', 'shipping_zone_name',
        'subtotal', 'delivery_charge', 'discount', 'total', 'cost_total',
        'payment_method', 'payment_status', 'status',
        'customer_note', 'admin_note', 'courier', 'tracking_number',
        'confirmed_at', 'shipped_at', 'delivered_at', 'cancelled_at',
    ];

    protected $casts = [
        'subtotal'       => 'decimal:2',
        'delivery_charge'=> 'decimal:2',
        'discount'       => 'decimal:2',
        'total'          => 'decimal:2',
        'cost_total'     => 'decimal:2',
        'confirmed_at'   => 'datetime',
        'shipped_at'     => 'datetime',
        'delivered_at'   => 'datetime',
        'cancelled_at'   => 'datetime',
    ];

    public const STATUSES = [
        'pending'    => 'Pending',
        'confirmed'  => 'Confirmed',
        'processing' => 'Processing',
        'shipped'    => 'Shipped',
        'delivered'  => 'Delivered',
        'cancelled'  => 'Cancelled',
        'returned'   => 'Returned',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function zone()
    {
        return $this->belongsTo(ShippingZone::class, 'shipping_zone_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function getIsOpenAttribute(): bool
    {
        return ! in_array($this->status, ['delivered', 'cancelled', 'returned']);
    }

    /** Gross profit on this order, from the cost snapshot taken at purchase. */
    public function getProfitAttribute(): ?float
    {
        return $this->cost_total === null
            ? null
            : (float) $this->subtotal - (float) $this->cost_total;
    }
}
