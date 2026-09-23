<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Review extends Model
{
    protected $fillable = [
        'product_id', 'user_id', 'order_id',
        'reviewer_name', 'reviewer_phone', 'reviewer_email',
        'rating', 'title', 'body',
        'is_verified', 'status', 'admin_reply',
    ];

    protected $casts = [
        'rating'      => 'integer',
        'is_verified' => 'boolean',
    ];

    public const STATUSES = [
        'pending'  => 'Waiting',
        'approved' => 'Published',
        'rejected' => 'Rejected',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /** "Sadia R." — a surname in full is more than a reviewer signed up for. */
    public function getDisplayNameAttribute(): string
    {
        $parts = preg_split('/\s+/', trim($this->reviewer_name));

        if (count($parts) < 2) {
            return $this->reviewer_name;
        }

        $last = array_pop($parts);

        return implode(' ', $parts) . ' ' . Str::upper(mb_substr($last, 0, 1)) . '.';
    }

    public function getInitialsAttribute(): string
    {
        $parts = preg_split('/\s+/', trim($this->reviewer_name));
        $first = mb_substr($parts[0] ?? '', 0, 1);
        $last  = count($parts) > 1 ? mb_substr(end($parts), 0, 1) : '';

        return Str::upper($first . $last);
    }
}
