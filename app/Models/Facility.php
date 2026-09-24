<?php

namespace App\Models;

use Database\Factories\FacilityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;

/**
 * Objekat - a school or open sports ground. Owns fields (courts) and the people
 * responsible for them (stewards).
 */
class Facility extends Model
{
    /** @use HasFactory<FacilityFactory> */
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'ownership', 'address', 'lat', 'lng', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'float',
            'lng' => 'float',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Facility $facility) {
            if (blank($facility->slug)) {
                $facility->slug = static::uniqueSlug($facility->name);
            }
        });

        // Delete courts through Eloquent (not the DB cascade) so each court's
        // deleting hook can clean its gallery and report photos off the disk.
        static::deleting(function (Facility $facility) {
            $facility->courts()->get()->each->delete();
        });
    }

    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'objekat';
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    public function courts(): HasMany
    {
        return $this->hasMany(Court::class);
    }

    public function stewards(): HasMany
    {
        return $this->hasMany(Steward::class);
    }

    public function reports(): HasManyThrough
    {
        return $this->hasManyThrough(Report::class, Court::class);
    }
}
