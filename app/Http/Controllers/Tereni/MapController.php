<?php

namespace App\Http\Controllers\Tereni;

use App\Enums\CourtType;
use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Support\Tereni\TereniStats;

/**
 * Public map of every active field. Fields without coordinates get no
 * marker (the JS skips null lat/lng) but still show in the card grid
 * and in the totals, so the landing numbers match the real directory.
 */
class MapController extends Controller
{
    public function index()
    {
        $courts = Court::active()
            ->with('facility')
            ->withCount(['openPublicReports as open_reports_count'])
            ->get()
            ->map(fn (Court $court) => $court->toCard())
            ->values();

        return view('tereni.map', [
            'courts' => $courts,
            'center' => config('site.tereni.map'),
            'types' => array_map(fn (CourtType $t) => [
                'value' => $t->value,
                'label' => $t->label(),
                'icon' => $t->icon(),
            ], CourtType::cases()),
            'stats' => TereniStats::summary(),
        ]);
    }
}
