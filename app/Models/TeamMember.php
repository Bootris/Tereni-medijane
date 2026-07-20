<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TeamMember extends Model
{
    protected static function booted(): void
    {
        static::creating(function (TeamMember $member) {
            $member->slug = $member->slug ?: Str::slug($member->name);
        });
    }

    protected $fillable = [
        'name', 'slug', 'title', 'bio', 'photo', 'email', 'phone', 'linkedin',
        'sort_order', 'visible',
    ];

    protected function casts(): array
    {
        return [
            'visible' => 'boolean',
        ];
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('visible', true)->orderBy('sort_order')->orderBy('name');
    }
}
