<?php

namespace App\Models;

use App\Enums\ReportCategory;
use App\Enums\ReportStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/**
 * Prijava — a citizen report about a field.
 *
 * Status transitions never mutate `status` directly: go through changeStatus()
 * so every step is recorded in report_status_changes, which is what the public
 * timeline renders. That public record is the whole point of the product.
 */
class Report extends Model
{
    protected $fillable = [
        'court_id', 'category', 'description', 'photo', 'status',
        'reporter_name', 'reporter_contact', 'reporter_ip',
        'is_public', 'is_flagged', 'resolution_note',
    ];

    protected function casts(): array
    {
        return [
            'category' => ReportCategory::class,
            'status' => ReportStatus::class,
            'is_public' => 'boolean',
            'is_flagged' => 'boolean',
        ];
    }

    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }

    public function statusChanges(): HasMany
    {
        return $this->hasMany(ReportStatusChange::class)->orderBy('created_at');
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    /**
     * Move the report to a new status and append it to the public history.
     * `note` is the per-step public comment (report_status_changes.note); the
     * standing public comment on the report itself is `resolution_note`, edited
     * separately, so the two never fight over the same field.
     */
    public function changeStatus(ReportStatus $status, ?string $note = null, ?User $by = null): ReportStatusChange
    {
        $this->status = $status;
        $this->save();

        return $this->statusChanges()->create([
            'status' => $status->value,
            'note' => $note ?: null,
            'changed_by' => $by?->getKey(),
            'created_at' => now(),
        ]);
    }

    /** Publish a moderated report and stamp the opening "Prijavljeno" step. */
    public function publish(?User $by = null): void
    {
        $this->is_public = true;
        $this->save();

        if ($this->statusChanges()->doesntExist()) {
            $this->statusChanges()->create([
                'status' => ($this->status ?? ReportStatus::Reported)->value,
                'note' => null,
                'changed_by' => $by?->getKey(),
                'created_at' => $this->created_at ?? now(),
            ]);
        }
    }

    public function photoUrl(): ?string
    {
        return $this->photo ? Storage::disk('public')->url($this->photo) : null;
    }
}
