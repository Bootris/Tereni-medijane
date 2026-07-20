<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\ResolvesMedia;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamMemberResource extends JsonResource
{
    use ResolvesMedia;

    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'title' => $this->title,
            'bio' => $this->bio,
            'photo_url' => $this->mediaUrl($this->photo),
            'email' => $this->email,
            'phone' => $this->phone,
            'linkedin' => $this->linkedin,
            'sort_order' => $this->sort_order,
        ];
    }
}
