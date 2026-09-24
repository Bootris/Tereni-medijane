<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Zaduženo lice - the person notified when a report lands on their facility. */
class Steward extends Model
{
    protected $fillable = [
        'facility_id', 'name', 'role', 'phone', 'email', 'notify',
    ];

    protected function casts(): array
    {
        return [
            'notify' => 'boolean',
        ];
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }
}
