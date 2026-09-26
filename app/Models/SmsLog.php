<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    protected $fillable = [
        'order_id', 'phone', 'event', 'message',
        'status', 'response', 'parts', 'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public const STATUSES = [
        'queued' => 'Queued',
        'sent'   => 'Sent',
        'failed' => 'Failed',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function scopeStatus($query, ?string $status)
    {
        return $status && isset(self::STATUSES[$status])
            ? $query->where('status', $status)
            : $query;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    /**
     * How many messages this actually costs.
     *
     * A plain English text fits 160 characters. The moment one Bangla letter
     * appears the whole message becomes Unicode and the limit drops to 70,
     * which is how a message you thought cost one taka costs three.
     */
    public static function partsFor(string $message): int
    {
        $unicode = (bool) preg_match('/[^\x00-\x7F]/u', $message);
        $length  = mb_strlen($message);

        if ($unicode) {
            return $length <= 70 ? 1 : (int) ceil($length / 67);
        }

        return $length <= 160 ? 1 : (int) ceil($length / 153);
    }

    public static function isUnicode(string $message): bool
    {
        return (bool) preg_match('/[^\x00-\x7F]/u', $message);
    }
}
