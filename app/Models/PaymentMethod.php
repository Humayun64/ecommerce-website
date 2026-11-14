<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

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
     * The online providers that have working code behind them. The list
     * mirrors App\Services\GatewayManager — add a provider there first.
     */
    public const GATEWAYS = [
        'sslcommerz' => 'SSLCommerz',
        'aamarpay'   => 'aamarPay',
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
     * An online method is usable only when its provider has a driver AND
     * its store ID and secret have been entered. Saying so here — in one
     * place — is what stops a half-configured gateway taking a real order.
     */
    public function getIsUsableAttribute(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        return in_array($this->driver, ['cod', 'manual'], true) || $this->is_connected;
    }

    public function getIsGatewayAttribute(): bool
    {
        return $this->driver === 'gateway';
    }

    /** A gateway with a known provider and both credentials filled in. */
    public function getIsConnectedAttribute(): bool
    {
        if (! $this->is_gateway || ! isset(self::GATEWAYS[$this->gateway])) {
            return false;
        }

        $creds = $this->credentials();

        return $creds['store_id'] !== '' && $creds['secret'] !== '';
    }

    public function getIsSandboxAttribute(): bool
    {
        return (bool) (($this->config ?? [])['sandbox'] ?? true);
    }

    /**
     * The provider login. The secret is kept encrypted in the database and
     * only decrypted here, at the moment it is needed.
     *
     * @return array{store_id:string, secret:string, sandbox:bool}
     */
    public function credentials(): array
    {
        $config = $this->config ?? [];
        $secret = '';

        if (! empty($config['secret'])) {
            try {
                $secret = Crypt::decryptString($config['secret']);
            } catch (\Throwable $e) {
                // Saved under a different APP_KEY: treat as not set, so the
                // method drops off checkout until the secret is typed again.
                $secret = '';
            }
        }

        return [
            'store_id' => trim((string) ($config['store_id'] ?? '')),
            'secret'   => $secret,
            'sandbox'  => (bool) ($config['sandbox'] ?? true),
        ];
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
