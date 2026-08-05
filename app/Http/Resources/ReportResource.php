<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\ResolvesMedia;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Public shape of a report + its status timeline. Reporter contact details are
 * deliberately never exposed.
 *
 * @mixin Report
 */
class ReportResource extends JsonResource
{
    use ResolvesMedia;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category' => $this->category->value,
            'category_label' => $this->category->label(),
            'description' => $this->description,
            'photo_url' => $this->mediaUrl($this->photo),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'resolution_note' => $this->resolution_note,
            'reported_at' => optional($this->created_at)->toIso8601String(),
            'timeline' => $this->whenLoaded('statusChanges', fn () => $this->statusChanges->map(fn ($change) => [
                'status' => $change->status->value,
                'status_label' => $change->status->label(),
                'note' => $change->note,
                'at' => optional($change->created_at)->toIso8601String(),
            ])->all()),
        ];
    }
}
