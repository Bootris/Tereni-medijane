<?php

namespace App\Models;

use App\Enums\ReportStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One entry in a report's public status timeline. */
class ReportStatusChange extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'report_id', 'status', 'note', 'changed_by', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReportStatus::class,
            'created_at' => 'datetime',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
