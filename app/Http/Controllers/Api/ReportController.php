<?php

namespace App\Http\Controllers\Api;

use App\Enums\ReportCategory;
use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Models\Report;
use App\Support\Tereni\ReportNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    /**
     * POST /api/v1/tereni/{court}/prijave — submit a report. Same anti-spam
     * posture as the web form: route throttle + mandatory photo + moderation.
     */
    public function store(Request $request, Court $court, ReportNotifier $notifier): JsonResponse
    {
        abort_unless($court->is_active, 404);

        $validated = $request->validate([
            'category' => ['required', Rule::in(array_keys(ReportCategory::options()))],
            'description' => ['nullable', 'string', 'max:2000'],
            'photo' => ['required', 'image', 'max:8192'],
            'reporter_name' => ['nullable', 'string', 'max:255'],
            'reporter_contact' => ['nullable', 'string', 'max:255'],
        ]);

        $report = Report::create([
            'court_id' => $court->id,
            'category' => $validated['category'],
            'description' => $validated['description'] ?? null,
            'photo' => $request->file('photo')->store('tereni/reports', 'public'),
            'reporter_name' => $validated['reporter_name'] ?? null,
            'reporter_contact' => $validated['reporter_contact'] ?? null,
            'reporter_ip' => $request->ip(),
            'is_public' => false,
        ]);

        $notifier->newReport($report);

        return response()->json([
            'ok' => true,
            'message' => 'Prijava je primljena i čeka moderaciju.',
        ], 201);
    }
}
