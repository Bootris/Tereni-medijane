<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\ResolvesMedia;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Light post — used in listings (no body, no SEO). */
class PostListResource extends JsonResource
{
    use ResolvesMedia;

    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'cover_url' => $this->mediaUrl($this->featured_image),
            'author' => $this->author_display,
            'reading_time' => $this->reading_time,
            'category' => $this->whenLoaded('category', fn () => $this->category ? [
                'slug' => $this->category->slug,
                'name' => $this->category->name,
            ] : null),
            'published_at' => optional($this->published_at)->toIso8601String(),
        ];
    }
}
