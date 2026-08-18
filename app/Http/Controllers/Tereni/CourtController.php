<?php

namespace App\Http\Controllers\Tereni;

use App\Enums\CourtAccess;
use App\Enums\CourtType;
use App\Enums\ReportCategory;
use App\Http\Controllers\Controller;
use App\Models\Court;
use Illuminate\Http\Request;

/** Public field page: details, gallery, and the moderated report timeline. */
class CourtController extends Controller
{
    /** Paginated directory of every active field, filterable via GET params. */
    public function index(Request $request)
    {
        $filters = [
            'sport' => CourtType::tryFrom((string) $request->query('sport'))?->value,
            'stanje' => in_array($request->query('stanje'), ['ok', 'issue'], true)
                ? $request->query('stanje') : null,
            'javno' => $request->boolean('javno') ? 1 : null,
            'q' => trim((string) $request->query('q')) ?: null,
        ];

        $courts = Court::active()
            ->with('facility')
            ->withCount(['openPublicReports as open_reports_count'])
            ->when($filters['sport'], fn ($q, $sport) => $q->where('type', $sport))
            ->when($filters['stanje'] === 'issue', fn ($q) => $q->whereHas('openPublicReports'))
            ->when($filters['stanje'] === 'ok', fn ($q) => $q->whereDoesntHave('openPublicReports'))
            ->when($filters['javno'], fn ($q) => $q->where('access', CourtAccess::Public))
            ->when($filters['q'], fn ($q, $term) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$term}%")
                ->orWhereHas('facility', fn ($f) => $f->where('name', 'like', "%{$term}%"))))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString()
            ->through(fn (Court $court) => $court->toCard());

        return view('tereni.list', [
            'courts' => $courts,
            'filters' => $filters,
            'types' => array_map(fn (CourtType $t) => [
                'value' => $t->value,
                'label' => $t->label(),
                'icon' => $t->icon(),
            ], CourtType::cases()),
        ]);
    }

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
