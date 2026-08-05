<?php

namespace App\Http\Controllers\Tereni;

use App\Enums\ReportCategory;
use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Models\Report;
use App\Support\Tereni\ReportNotifier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Citizen report submission — no registration. Anti-spam: per-IP rate limit
 * (route throttle), mandatory photo, honeypot, and moderation before the report
 * appears on the public timeline.
 */
class ReportController extends Controller
{
    public function store(Request $request, Court $court, ReportNotifier $notifier)
    {
        abort_unless($court->is_active, 404);

        // Honeypot: hidden field real visitors never fill.
        if ($request->filled('website')) {
            return back()->with('report_success', true);
        }

        $validated = $request->validate([
            'category' => ['required', Rule::in(array_keys(ReportCategory::options()))],
            'description' => ['nullable', 'string', 'max:2000'],
            'photo' => ['required', 'image', 'max:8192'], // proof is mandatory — anti-spam
            'reporter_name' => ['nullable', 'string', 'max:255'],
            'reporter_contact' => ['nullable', 'string', 'max:255'],
        ], [
            'photo.required' => 'Fotografija je obavezna.',
        ]);

        $report = Report::create([
            'court_id' => $court->id,
            'category' => $validated['category'],
            'description' => $validated['description'] ?? null,
            'photo' => $request->file('photo')->store('tereni/reports', 'public'),
            'reporter_name' => $validated['reporter_name'] ?? null,
            'reporter_contact' => $validated['reporter_contact'] ?? null,
            'reporter_ip' => $request->ip(),
            'is_public' => false, // held for moderation
        ]);

        $notifier->newReport($report);

        return back()->with('report_success', true);
    }

    /** Public "flag" on an already-visible report — draws moderator attention. */
    public function flag(Report $report)
    {
        abort_unless($report->is_public, 404);

        $report->forceFill(['is_flagged' => true])->save();

        return back()->with('report_flagged', true);
    }
}
