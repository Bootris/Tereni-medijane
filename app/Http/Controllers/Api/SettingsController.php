<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /**
     * GET /api/v1/settings — brand + contact + capabilities for any frontend.
     * Keys mirror what Admin → Site settings stores; unset keys return null so
     * the contract stays forward-compatible as new fields are added.
     */
    public function show()
    {
        $s = Setting::allCached();
        $preset = config('site.preset', 'generic');

        return response()->json([
            'name' => $s['site_name'] ?? config('site.name'),
            'tagline' => $s['tagline'] ?? config('site.tagline') ?: null,
            'logo_url' => isset($s['logo']) ? url(Storage::disk('public')->url($s['logo'])) : null,
            'theme' => [
                'primary' => $s['theme_primary'] ?? config('site.brand.primary'),
                'accent' => $s['theme_accent'] ?? config('site.brand.accent'),
                'font' => $s['theme_font'] ?? config('site.brand.font'),
            ],
            'contact' => [
                'email' => $s['email'] ?? null,
                'phone' => $s['phone'] ?? null,
                'address' => $s['address'] ?? null,
                'hours' => $s['working_hours'] ?? null,
            ],
            'socials' => [
                'facebook' => $s['facebook'] ?? null,
                'instagram' => $s['instagram'] ?? null,
                'linkedin' => $s['linkedin_url'] ?? null,
                'youtube' => $s['youtube'] ?? null,
            ],
            'maps_embed' => $s['map_embed'] ?? null,
            'calendly_url' => $s['calendly_url'] ?? null,
            // Lets a single frontend codebase show/hide sections per client.
            'features' => config('site.features'),
            'sections' => config("site.presets.$preset.sections", []),
        ]);
    }
}
