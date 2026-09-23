<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnRequest extends Model
{
    protected $fillable = [
        'order_id', 'order_item_id', 'user_id',
        'product_name', 'variant_label',
        'customer_name', 'customer_phone', 'customer_email',
        'quantity', 'reason', 'note', 'photo',
        'refund_amount', 'status', 'admin_note', 'restocked',
        'approved_at', 'rejected_at', 'refunded_at',
    ];

    protected $casts = [
        'refund_amount' => 'decimal:2',
        'restocked'     => 'boolean',
        'approved_at'   => 'datetime',
        'rejected_at'   => 'datetime',
        'refunded_at'   => 'datetime',
    ];

    public const STATUSES = [
        'pending'  => 'Pending',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'refunded' => 'Refunded',
    ];

    /**
     * The reasons a customer can pick. Keeping them to a fixed list means
     * the admin screen can count them and you can see which product keeps
     * coming back, which free text would never tell you.
     */
    public const REASONS = [
        'not_as_described' => 'Not as described',
        'defective'        => 'Product is defective/damaged',
        'wrong_item'       => 'Wrong item sent',
        'missing_parts'    => 'Parts or items missing',
        'changed_mind'     => 'Changed my mind',
        'other'            => 'Something else',
    ];

    /** Reasons that are the shop's fault, so the item comes back unsellable. */
    public const FAULT_REASONS = ['defective', 'wrong_item', 'missing_parts'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function item()
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
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
            $q->where('customer_name', 'like', "%{$term}%")
              ->orWhere('customer_phone', 'like', "%{$term}%")
              ->orWhere('customer_email', 'like', "%{$term}%")
              ->orWhere('product_name', 'like', "%{$term}%")
              ->orWhereHas('order', fn ($o) => $o->where('order_number', 'like', "%{$term}%"));
        });
    }

    public function getReasonLabelAttribute(): string
    {
        return self::REASONS[$this->reason] ?? $this->reason;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function getIsOpenAttribute(): bool
    {
        return in_array($this->status, ['pending', 'approved'], true);
    }

    /** Our fault, so it is not the customer being difficult. */
    public function getIsOurFaultAttribute(): bool
    {
        return in_array($this->reason, self::FAULT_REASONS, true);
    }
}
