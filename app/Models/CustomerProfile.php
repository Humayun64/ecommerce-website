<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerProfile extends Model
{
    protected $fillable = ['phone', 'is_blocked', 'block_reason', 'blocked_at', 'note'];

    protected $casts = [
        'is_blocked' => 'boolean',
        'blocked_at' => 'datetime',
    ];

    public static function normalise(?string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '880')) {
            $digits = '0' . substr($digits, 3);
        }

        return $digits;
    }

    public static function isBlocked(?string $phone): bool
    {
        $phone = static::normalise($phone);

        return $phone
            ? static::where('phone', $phone)->where('is_blocked', true)->exists()
            : false;
    }
}
