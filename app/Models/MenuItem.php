<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $fillable = [
        'location', 'column', 'label', 'url', 'new_tab', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'new_tab'   => 'boolean',
        'is_active' => 'boolean',
        'column'    => 'integer',
    ];

    public const LOCATIONS = ['header' => 'Main menu', 'footer' => 'Footer'];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFor($query, string $location)
    {
        return $query->where('location', $location);
    }

    /** Relative paths stay relative so the site works on any domain. */
    public function getHrefAttribute(): string
    {
        $url = trim($this->url);

        if ($url === '') {
            return '#';
        }

        if (preg_match('#^(https?://|mailto:|tel:|\#)#i', $url)) {
            return $url;
        }

        return url($url);
    }

    public function getIsExternalAttribute(): bool
    {
        return (bool) preg_match('#^https?://#i', trim($this->url));
    }
}
