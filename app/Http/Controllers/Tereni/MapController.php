<?php

namespace App\Http\Controllers\Tereni;

use App\Http\Controllers\Controller;
use App\Models\Court;

/** Public map of every active, locatable field. */
class MapController extends Controller
{
    public function index()
    {
        $courts = Court::active()
            ->locatable()
            ->with('facility')
            ->get()
            ->map(fn (Court $court) => [
                'name' => $court->name,
                'facility' => $court->facility?->name,
                'type' => $court->type->label(),
                'access' => $court->access->label(),
                'lat' => $court->latitude(),
                'lng' => $court->longitude(),
                'url' => route('tereni.court', $court),
            ])
            ->values();

        return view('tereni.map', [
            'courts' => $courts,
            'center' => config('site.tereni.map'),
        ]);
    }
}
