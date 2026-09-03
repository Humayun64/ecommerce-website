<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'product_variant_id',
        'name', 'variant_label', 'sku', 'brand_name',
        'unit_price', 'unit_cost', 'quantity', 'line_total',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'unit_cost'  => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /** May be null if the product was deleted. The snapshot above still stands. */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
