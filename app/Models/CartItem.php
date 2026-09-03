<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['cart_id', 'product_id', 'product_variant_id', 'quantity'];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Price is read live, never stored on the cart. If you change a price in
     * admin, open carts pick it up. Orders take a snapshot instead.
     */
    public function getUnitPriceAttribute(): float
    {
        return (float) ($this->variant?->price ?? $this->product?->price ?? 0);
    }

    public function getLineTotalAttribute(): float
    {
        return $this->unit_price * $this->quantity;
    }

    public function getAvailableAttribute(): int
    {
        return (int) ($this->variant?->stock ?? $this->product?->stock ?? 0);
    }

    public function getLabelAttribute(): ?string
    {
        return $this->variant?->name;
    }
}
