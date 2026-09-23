<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tag extends Model
{
    protected $fillable = ['name', 'slug'];

    protected static function booted(): void
    {
        static::saving(function (Tag $tag) {
            if (blank($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }

    public function posts()
    {
        return $this->belongsToMany(Post::class);
    }

    /** "Korean skincare, serums" → tag models, reusing any that exist. */
    public static function fromList(?string $list): array
    {
        $names = collect(explode(',', (string) $list))
            ->map(fn ($n) => trim($n))
            ->filter()
            ->unique()
            ->take(12);

        return $names->map(function ($name) {
            return static::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name])->id;
        })->all();
    }
}
