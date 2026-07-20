<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /**
     * GET /api/v1/settings — brand + contact config for any frontend.
     * Unknown keys return null so the contract is forward-compatible as
     * new Settings fields are added in the admin.
     */
    public function show()
    {
        $s = Setting::allCached();

        return response()->json([
            'name' => $s['site_name'] ?? config('app.name'),
            'tagline' => $s['tagline'] ?? null,
            'logo_url' => isset($s['logo']) ? url(Storage::disk('public')->url($s['logo'])) : null,
            'theme' => [
                'primary' => $s['theme_primary'] ?? null,
                'accent' => $s['theme_accent'] ?? null,
                'font' => $s['theme_font'] ?? null,
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
                'linkedin' => $s['linkedin'] ?? null,
                'youtube' => $s['youtube'] ?? null,
            ],
            'maps_embed' => $s['maps_embed'] ?? null,
            'calendly_url' => $s['calendly_url'] ?? null,
        ]);
    }
}
