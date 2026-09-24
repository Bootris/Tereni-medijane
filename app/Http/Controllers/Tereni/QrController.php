<?php

namespace App\Http\Controllers\Tereni;

use App\Http\Controllers\Controller;
use App\Models\Court;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * QR code (SVG) that encodes a field's public URL - this is what gets printed
 * on the plaque bolted to the fence. SVG needs no imagick extension.
 */
class QrController extends Controller
{
    public function show(Court $court)
    {
        $svg = QrCode::format('svg')
            ->size(512)
            ->margin(1)
            ->errorCorrection('M')
            ->generate(route('tereni.court', $court));

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=86400',
            'Content-Disposition' => 'inline; filename="qr-'.$court->slug.'.svg"',
        ]);
    }
}
