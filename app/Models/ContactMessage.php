<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = [
        'user_id', 'name', 'phone', 'email', 'topic', 'order_number', 'message',
        'status', 'admin_note', 'read_at', 'replied_at', 'handled_by',
        'ip', 'user_agent',
    ];

    protected $casts = [
        'read_at'    => 'datetime',
        'replied_at' => 'datetime',
    ];

    public const STATUSES = [
        'new'      => 'New',
        'read'     => 'Read',
        'replied'  => 'Replied',
        'spam'     => 'Spam',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function handler()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function scopeStatus($query, ?string $status)
    {
        return $status && isset(self::STATUSES[$status])
            ? $query->where('status', $status)
            : $query;
    }

    /** The inbox hides spam unless you go looking for it. */
    public function scopeInbox($query)
    {
        return $query->where('status', '!=', 'spam');
    }

    public function scopeSearch($query, ?string $term)
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('phone', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%")
              ->orWhere('order_number', 'like', "%{$term}%")
              ->orWhere('message', 'like', "%{$term}%");
        });
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function getIsUnreadAttribute(): bool
    {
        return $this->status === 'new';
    }

    /** A one-tap reply on a phone, which is how these actually get answered. */
    public function getWhatsappLinkAttribute(): ?string
    {
        if (! $this->phone) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $this->phone);

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = '88' . $digits;
        }

        return 'https://wa.me/' . $digits;
    }
}
