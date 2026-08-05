<?php

namespace App\Http\Controllers\Tereni;

use App\Enums\CourtType;
use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Support\Tereni\TereniStats;

/** Public map of every active, locatable field. */
class MapController extends Controller
{
    public function index()
    {
        $closed = [ReportStatus::Resolved->value, ReportStatus::Rejected->value];

        $courts = Court::active()
            ->locatable()
            ->with('facility')
            ->withCount([
                'reports as open_reports_count' => fn ($q) => $q
                    ->where('is_public', true)
                    ->whereNotIn('status', $closed),
            ])
            ->get()
            ->map(fn (Court $court) => [
                'name' => $court->name,
                'facility' => $court->facility?->name,
                'type' => $court->type->value,
                'type_label' => $court->type->label(),
                'icon' => $court->type->icon(),
                'access' => $court->access->value,
                'access_label' => $court->access->label(),
                'has_issue' => $court->open_reports_count > 0,
                'lat' => $court->latitude(),
                'lng' => $court->longitude(),
                'url' => route('tereni.court', $court),
            ])
            ->values();

        return view('tereni.map', [
            'courts' => $courts,
            'center' => config('site.tereni.map'),
            'types' => CourtType::options(),
            'stats' => TereniStats::summary(),
        ]);
    }
}
