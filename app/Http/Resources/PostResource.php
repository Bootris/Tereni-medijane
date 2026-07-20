<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\ResolvesMedia;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Full post — used on the single-post endpoint (includes body). */
class PostResource extends JsonResource
{
    use ResolvesMedia;

    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'body_html' => $this->body,
            'cover_url' => $this->mediaUrl($this->featured_image),
            'video_embed' => $this->video_embed_url,
            'author' => $this->author_display,
            'reading_time' => $this->reading_time,
            'category' => $this->whenLoaded('category', fn () => $this->category ? [
                'slug' => $this->category->slug,
                'name' => $this->category->name,
            ] : null),
            'published_at' => optional($this->published_at)->toIso8601String(),
            'seo' => [
                'title' => $this->seo_title ?: $this->title,
                'description' => $this->seo_description ?: $this->excerpt,
            ],
        ];
    }
}
