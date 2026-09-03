<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = ['user_id', 'token'];

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function loadLines(): self
    {
        return $this->load([
            'items.product.primaryImage',
            'items.product.brand',
            'items.variant',
        ]);
    }

    public function getSubtotalAttribute(): float
    {
        return (float) $this->items->sum(fn (CartItem $item) => $item->line_total);
    }

    public function getCountAttribute(): int
    {
        return (int) $this->items->sum('quantity');
    }

    /** Lines whose stock dropped below what the customer has in the cart. */
    public function problemLines()
    {
        return $this->items->filter(fn (CartItem $item) => $item->quantity > $item->available);
    }
}
