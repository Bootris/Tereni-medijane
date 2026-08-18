<?php

namespace App\Models;

use App\Enums\CourtAccess;
use App\Enums\CourtType;
use App\Enums\ReportStatus;
use Database\Factories\CourtFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Teren — a single field. Its `slug` is the QR-code target: scanning the plaque
 * on the fence opens /teren/{slug}.
 */
class Court extends Model
{
    /** @use HasFactory<CourtFactory> */
    use HasFactory;

    protected $fillable = [
        'facility_id', 'name', 'slug', 'type', 'surface', 'dimensions',
        'has_lighting', 'access', 'description', 'gallery', 'lat', 'lng', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'has_lighting' => 'boolean',
            'is_active' => 'boolean',
            'gallery' => 'array',
            'lat' => 'float',
            'lng' => 'float',
            'type' => CourtType::class,
            'access' => CourtAccess::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Court $court) {
            if (blank($court->slug)) {
                $court->slug = static::uniqueSlug($court->name);
            }
        });

        // Gallery images removed in the admin form disappear from the array;
        // remove their files too so the public disk never accumulates orphans.
        // Deletion is guarded — see deleteGalleryFiles().
        static::updated(function (Court $court) {
            $removed = array_diff(
                $court->getOriginal('gallery') ?? [],
                $court->gallery ?? [],
            );

            static::deleteGalleryFiles(array_values($removed));
        });

        // Delete reports through Eloquent (not the DB cascade) so each one
        // runs its own guarded photo cleanup; then clear the gallery files.
        static::deleting(function (Court $court) {
            $court->reports()->get()->each->delete();

            static::deleteGalleryFiles($court->gallery ?? [], exceptCourtId: $court->getKey());
        });
    }

    /**
     * Delete gallery images from the public disk — defensively. The stored
     * paths round-trip through a client-controllable form field, so only
     * files inside this module's own directory are ever deleted, and never
     * one that another court's gallery still references.
     */
    public static function deleteGalleryFiles(array $paths, ?int $exceptCourtId = null): void
    {
        foreach ($paths as $path) {
            if (! is_string($path) || ! str_starts_with($path, 'tereni/courts/')) {
                continue;
            }

            $stillReferenced = static::query()
                ->when($exceptCourtId !== null, fn ($q) => $q->whereKeyNot($exceptCourtId))
                ->whereJsonContains('gallery', $path)
                ->exists();

            if (! $stillReferenced) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'teren';
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    /** Reports visible on the public field page. */
    public function publicReports(): HasMany
    {
        return $this->reports()->where('is_public', true)->latest();
    }

    /** Open public reports — the ones that flag a court as "ima problem". */
    public function openPublicReports(): HasMany
    {
        return $this->reports()
            ->where('is_public', true)
            ->whereNotIn('status', [ReportStatus::Resolved->value, ReportStatus::Rejected->value]);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Fields that can actually be shown on the map need coordinates — their
     * own, or inherited from the facility (see latitude()/longitude()).
     */
    public function scopeLocatable(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->where(fn (Builder $own) => $own->whereNotNull('lat')->whereNotNull('lng'))
                ->orWhereHas('facility', fn ($f) => $f->whereNotNull('lat')->whereNotNull('lng'));
        });
    }

    /** Field coords, falling back to the parent facility's. */
    public function latitude(): ?float
    {
        return $this->lat ?? $this->facility?->lat;
    }

    public function longitude(): ?float
    {
        return $this->lng ?? $this->facility?->lng;
    }

    /**
     * The array shape the public card/map UI consumes (Blade cards + map JSON).
     * `has_issue` expects an eager `openPublicReports as open_reports_count`.
     */
    public function toCard(): array
    {
        return [
            'name' => $this->name,
            'facility' => $this->facility?->name,
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'icon' => $this->type->icon(),
            'access' => $this->access->value,
            'access_label' => $this->access->label(),
            'has_issue' => ($this->open_reports_count ?? 0) > 0,
            'photos' => array_map(
                fn (string $path) => Storage::disk('public')->url($path),
                $this->gallery ?? [],
            ),
            'lat' => $this->latitude(),
            'lng' => $this->longitude(),
            'url' => route('tereni.court', $this),
        ];
    }
}
