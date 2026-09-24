<?php

namespace App\Support\Tereni;

use App\Enums\ReportStatus;
use App\Models\Report;
use App\Models\ReportStatusChange;
use Illuminate\Support\Facades\Cache;

/**
 * Report statistics for the public stats bar (map page) and the admin KPI
 * dashboard. Cached briefly; flushed on every status change / publish so the
 * public numbers never lag a fresh "Rešeno".
 */
class TereniStats
{
    private const CACHE_KEY = 'tereni_stats';

    /** @return array{pending:int,open:int,resolved_total:int,resolved_month:int,avg_days:float|null} */
    public static function summary(): array
    {
        return Cache::remember(self::CACHE_KEY, 600, fn (): array => self::compute());
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /** @return array{pending:int,open:int,resolved_total:int,resolved_month:int,avg_days:float|null} */
    public static function compute(): array
    {
        $closed = [ReportStatus::Resolved->value, ReportStatus::Rejected->value];

        // First "Rešeno" step per report - resolution moments for month/average.
        $resolutions = ReportStatusChange::query()
            ->where('status', ReportStatus::Resolved->value)
            ->whereHas('report', fn ($q) => $q->where('is_public', true))
            ->with('report:id,created_at')
            ->orderBy('created_at')
            ->get()
            ->unique('report_id');

        $daysToResolve = $resolutions
            ->filter(fn (ReportStatusChange $c) => $c->report?->created_at && $c->created_at)
            ->map(fn (ReportStatusChange $c) => $c->report->created_at->diffInSeconds($c->created_at) / 86400)
            ->filter(fn (float $days) => $days >= 0);

        return [
            // Awaiting moderation (admin-only number).
            'pending' => Report::where('is_public', false)->count(),
            // Publicly visible and still unresolved.
            'open' => Report::public()->whereNotIn('status', $closed)->count(),
            'resolved_total' => Report::public()->where('status', ReportStatus::Resolved->value)->count(),
            'resolved_month' => $resolutions
                ->filter(fn (ReportStatusChange $c) => $c->created_at?->greaterThanOrEqualTo(now()->startOfMonth()))
                ->count(),
            'avg_days' => $daysToResolve->isEmpty() ? null : round($daysToResolve->avg(), 1),
        ];
    }
}
