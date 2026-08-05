<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourtResource;
use App\Http\Resources\ReportResource;
use App\Models\Court;

/**
 * Read-only Tereni contract for external frontends (Astro/Next). Mirrors the
 * Blade pages; changing a response shape means a /v2 group (see docs/BACKEND.md).
 */
class CourtController extends Controller
{
    /** GET /api/v1/tereni — all active, locatable fields (for the map). */
    public function index()
    {
        $courts = Court::active()
            ->locatable()
            ->with('facility')
            ->orderBy('name')
            ->get();

        return CourtResource::collection($courts);
    }

    /** GET /api/v1/tereni/{court} — one field + its public report timeline. */
    public function show(Court $court)
    {
        abort_unless($court->is_active, 404);

        $court->load(['facility', 'publicReports.statusChanges']);

        return (new CourtResource($court))
            ->additional([
                'reports' => ReportResource::collection($court->publicReports),
            ]);
    }
}
