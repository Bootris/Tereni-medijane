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
        'is_public', 'is_flagged', 'resolution_note', 'resolution_photo',
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

    protected static function booted(): void
    {
        // Keep the public disk in sync with the record: replacing or clearing
        // a photo (moderation) removes the old file, deleting the report
        // removes its files. Deletion is guarded — see deletePhotoFile().
        static::updated(function (Report $report) {
            foreach (['photo', 'resolution_photo'] as $column) {
                $old = $report->getOriginal($column);
                $new = $report->{$column};

                if (! $old || $old === $new) {
                    continue;
                }

                // Stale concurrent edit: if the "new" photo doesn't actually
                // exist on disk, this save is reviving a dead path — don't
                // also destroy the file the other session just stored.
                if ($new && ! Storage::disk('public')->exists($new)) {
                    continue;
                }

                static::deletePhotoFile($old, exceptReportId: $report->getKey());
            }
        });

        static::deleted(function (Report $report) {
            foreach (array_filter([$report->photo, $report->resolution_photo]) as $file) {
                static::deletePhotoFile($file, exceptReportId: $report->getKey());
            }

            \App\Support\Tereni\TereniStats::flush();
        });

        static::created(fn () => \App\Support\Tereni\TereniStats::flush());
    }

    /**
     * Delete a report photo from the public disk — defensively. The stored
     * path round-trips through a client-controllable form field, so only
     * files inside this module's own directory are ever deleted, and never
     * one that another report still references.
     */
    public static function deletePhotoFile(string $path, ?int $exceptReportId = null): void
    {
        if (! str_starts_with($path, 'tereni/reports/')) {
            return;
        }

        $stillReferenced = static::query()
            ->when($exceptReportId !== null, fn ($q) => $q->whereKeyNot($exceptReportId))
            ->where(fn ($q) => $q->where('photo', $path)->orWhere('resolution_photo', $path))
            ->exists();

        if (! $stillReferenced) {
            Storage::disk('public')->delete($path);
        }
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

        $change = $this->statusChanges()->create([
            'status' => $status->value,
            'note' => $note ?: null,
            'changed_by' => $by?->getKey(),
            'created_at' => now(),
        ]);

        \App\Support\Tereni\TereniStats::flush();

        return $change;
    }

    /** Publish a moderated report and stamp the opening "Prijavljeno" step. */
    public function publish(?User $by = null): void
    {
        $this->is_public = true;
        $this->save();

        \App\Support\Tereni\TereniStats::flush();

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

    /** "Posle popravke" photo — the public before/after pair. */
    public function resolutionPhotoUrl(): ?string
    {
        return $this->resolution_photo ? Storage::disk('public')->url($this->resolution_photo) : null;
    }
}
