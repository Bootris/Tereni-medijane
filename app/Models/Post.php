<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Post extends Model
{
    protected $fillable = [
        'title', 'slug', 'excerpt', 'body', 'category_id', 'user_id', 'author_name',
        'status', 'published_at', 'featured_image', 'video_url', 'show_on_home',
        'seo_title', 'seo_description',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'show_on_home' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        $clear = fn () => Cache::forget('site_latest_posts');
        static::saved($clear);
        static::deleted($clear);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function getAuthorDisplayAttribute(): ?string
    {
        return $this->author?->name ?? $this->author_name;
    }

    public function getReadingTimeAttribute(): int
    {
        return max(1, (int) ceil(Str::wordCount(strip_tags($this->body)) / 200));
    }

    /** YouTube/Vimeo URL converted to an embeddable iframe src, or null. */
    public function getVideoEmbedUrlAttribute(): ?string
    {
        if (! $this->video_url) {
            return null;
        }

        if (preg_match('~(?:youtube\.com/(?:watch\?v=|shorts/|embed/)|youtu\.be/)([\w-]{11})~', $this->video_url, $m)) {
            return "https://www.youtube-nocookie.com/embed/{$m[1]}";
        }

        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $this->video_url, $m)) {
            return "https://player.vimeo.com/video/{$m[1]}";
        }

        return null;
    }
}
