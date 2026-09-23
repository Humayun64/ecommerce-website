<?php

namespace App\Models;

use App\Support\Markup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Post extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'blog_category_id', 'user_id',
        'title', 'slug', 'excerpt', 'content', 'cover_image', 'cover_alt',
        'status', 'published_at', 'is_featured',
        'reading_minutes',
        'focus_keyword', 'meta_title', 'meta_description',
        'og_title', 'og_description', 'canonical_url', 'is_indexable',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_featured'  => 'boolean',
        'is_indexable' => 'boolean',
    ];

    public const STATUSES = [
        'draft'     => 'Draft',
        'published' => 'Published',
        'scheduled' => 'Scheduled',
    ];

    protected static function booted(): void
    {
        static::saving(function (Post $post) {
            if (blank($post->slug)) {
                $post->slug = Str::slug($post->title);
            }

            // 200 words a minute is the usual reading pace.
            $words = str_word_count(strip_tags((string) $post->content));
            $post->reading_minutes = max(1, (int) ceil($words / 200));

            // A post marked published with no date gets one now; a future
            // date means it is scheduled, whatever the dropdown said.
            if ($post->status === 'published' && ! $post->published_at) {
                $post->published_at = now();
            }

            if ($post->published_at && $post->published_at->isFuture() && $post->status !== 'draft') {
                $post->status = 'scheduled';
            }

            if ($post->status === 'scheduled' && $post->published_at && $post->published_at->isPast()) {
                $post->status = 'published';
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    /* ---------- scopes ---------- */

    public function scopeLive($query)
    {
        return $query->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public function scopeSearch($query, ?string $term)
    {
        if (! $term = trim((string) $term)) {
            return $query;
        }

        return $query->where(fn ($q) => $q
            ->where('title', 'like', "%{$term}%")
            ->orWhere('excerpt', 'like', "%{$term}%")
            ->orWhere('content', 'like', "%{$term}%"));
    }

    /* ---------- display ---------- */

    public function getUrlAttribute(): string
    {
        return route('blog.show', $this->slug);
    }

    public function getCoverUrlAttribute(): ?string
    {
        return $this->cover_image ? Storage::url($this->cover_image) : null;
    }

    public function getBodyHtmlAttribute(): string
    {
        return Markup::render($this->content);
    }

    public function getSummaryAttribute(): string
    {
        return $this->excerpt
            ?: Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags(Markup::render($this->content)))), 160);
    }

    public function getSeoTitleAttribute(): string
    {
        return $this->meta_title ?: $this->title;
    }

    public function getSeoDescriptionAttribute(): string
    {
        return $this->meta_description ?: $this->summary;
    }

    public function getIsLiveAttribute(): bool
    {
        return $this->status === 'published'
            && (! $this->published_at || $this->published_at->isPast());
    }

    /** Headings in the body, for the contents list on long posts. */
    public function getHeadingsAttribute(): array
    {
        preg_match_all('/^##\s+(.+)$/m', (string) $this->content, $matches);

        return collect($matches[1] ?? [])
            ->map(fn ($text) => ['text' => trim($text), 'anchor' => Str::slug(trim($text))])
            ->all();
    }
}
