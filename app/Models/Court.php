<?php

namespace App\Models;

use App\Enums\CourtAccess;
use App\Enums\CourtType;
use Database\Factories\CourtFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Fields that can actually be shown on the map need coordinates. */
    public function scopeLocatable(Builder $query): Builder
    {
        return $query->whereNotNull('lat')->whereNotNull('lng');
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
}
