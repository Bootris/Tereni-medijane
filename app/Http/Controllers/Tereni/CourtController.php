<?php

namespace App\Http\Controllers\Tereni;

use App\Enums\ReportCategory;
use App\Http\Controllers\Controller;
use App\Models\Court;

/** Public field page: details, gallery, and the moderated report timeline. */
class CourtController extends Controller
{
    public function show(Court $court)
    {
        abort_unless($court->is_active, 404);

        $court->load(['facility', 'publicReports.statusChanges']);

        return view('tereni.court', [
            'court' => $court,
            'reports' => $court->publicReports,
            'categories' => ReportCategory::options(),
        ]);
    }
}
