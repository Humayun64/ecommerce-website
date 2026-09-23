<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id', 'payment_method_id', 'method_code', 'method_name',
        'amount', 'sender_number', 'transaction_id',
        'status', 'admin_note', 'verified_at', 'verified_by',
        'reference', 'payload',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'verified_at' => 'datetime',
        'payload'     => 'array',
    ];

    public const STATUSES = [
        'pending'  => 'Waiting to be checked',
        'verified' => 'Verified',
        'rejected' => 'Rejected',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function method()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function scopeStatus($query, ?string $status)
    {
        return $status && isset(self::STATUSES[$status])
            ? $query->where('status', $status)
            : $query;
    }

    public function scopeSearch($query, ?string $term)
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('transaction_id', 'like', "%{$term}%")
              ->orWhere('sender_number', 'like', "%{$term}%")
              ->orWhereHas('order', function ($o) use ($term) {
                  $o->where('order_number', 'like', "%{$term}%")
                    ->orWhere('customer_name', 'like', "%{$term}%")
                    ->orWhere('customer_phone', 'like', "%{$term}%");
              });
        });
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }
}
