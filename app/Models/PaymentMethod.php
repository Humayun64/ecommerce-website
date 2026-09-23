<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'code', 'name', 'tagline', 'driver', 'gateway',
        'account_number', 'account_type', 'instructions', 'logo', 'accent',
        'needs_sender', 'needs_txn',
        'charge_percent', 'min_amount',
        'is_active', 'is_default', 'sort_order', 'config',
    ];

    protected $casts = [
        'needs_sender'   => 'boolean',
        'needs_txn'      => 'boolean',
        'is_active'      => 'boolean',
        'is_default'     => 'boolean',
        'charge_percent' => 'decimal:2',
        'min_amount'     => 'decimal:2',
        'config'         => 'array',
    ];

    public const DRIVERS = [
        'cod'     => 'Cash on delivery',
        'manual'  => 'Manual — customer sends money, then types the transaction ID',
        'gateway' => 'Automatic — customer pays on the provider’s page',
    ];

    /**
     * Gateways we have a place for but have not wired up. Choosing one only
     * records the intent; the method stays unusable until the API is built,
     * and `usable()` below is what keeps it off the checkout page.
     */
    public const GATEWAYS = [
        'sslcommerz' => 'SSLCommerz',
        'bkash'      => 'bKash (direct API)',
        'nagad'      => 'Nagad (direct API)',
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Can a customer actually pay with this right now?
     *
     * A gateway row is never usable until someone writes the driver for it,
     * however complete its settings look. Saying so here — in one place —
     * is what stops a half-configured gateway taking a real order.
     */
    public function getIsUsableAttribute(): bool
    {
        return $this->is_active && in_array($this->driver, ['cod', 'manual'], true);
    }

    public function getIsManualAttribute(): bool
    {
        return $this->driver === 'manual';
    }

    public function getDriverLabelAttribute(): string
    {
        return self::DRIVERS[$this->driver] ?? $this->driver;
    }

    /** Extra the customer pays for choosing this method, e.g. a cash-out fee. */
    public function chargeOn(float $amount): float
    {
        return round($amount * ((float) $this->charge_percent / 100), 2);
    }
}
