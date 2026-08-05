<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\ResolvesMedia;
use App\Models\Court;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Court
 */
class CourtResource extends JsonResource
{
    use ResolvesMedia;

    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'surface' => $this->surface,
            'dimensions' => $this->dimensions,
            'has_lighting' => $this->has_lighting,
            'access' => $this->access->value,
            'access_label' => $this->access->label(),
            'description' => $this->description,
            'lat' => $this->latitude(),
            'lng' => $this->longitude(),
            'gallery' => collect($this->gallery ?? [])
                ->map(fn ($path) => $this->mediaUrl($path))
                ->all(),
            'facility' => $this->whenLoaded('facility', fn () => $this->facility ? [
                'slug' => $this->facility->slug,
                'name' => $this->facility->name,
                'ownership' => $this->facility->ownership,
                'address' => $this->facility->address,
            ] : null),
            'report_url' => route('tereni.court', $this->resource),
            'qr_url' => route('tereni.court.qr', $this->resource),
        ];
    }
}
