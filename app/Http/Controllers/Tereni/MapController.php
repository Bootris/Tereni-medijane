<?php

namespace App\Http\Controllers\Tereni;

use App\Enums\CourtType;
use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Support\Tereni\TereniStats;

/** Public map of every active, locatable field. */
class MapController extends Controller
{
    public function index()
    {
        $courts = Court::active()
            ->locatable()
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
