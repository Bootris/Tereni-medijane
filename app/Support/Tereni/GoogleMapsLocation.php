<?php

namespace App\Support\Tereni;

use Illuminate\Support\Facades\Http;

/**
 * Pulls coordinates out of whatever gets pasted from Google Maps:
 * bare coordinates ("43.3209, 21.9033"), a full maps URL (@lat,lng /
 * !3dlat!4dlng / ?q=lat,lng), or a maps.app.goo.gl share link, which is
 * resolved over HTTP by following redirects.
 */
class GoogleMapsLocation
{
    private const SHORT_LINK_HOSTS = ['maps.app.goo.gl', 'goo.gl', 'g.co'];

    /** @return array{lat: float, lng: float}|null */
    public static function parse(?string $input): ?array
    {
        $input = trim((string) $input);
        if ($input === '') {
            return null;
        }

        return self::extract($input) ?? self::resolveShortLink($input);
    }

    /** @return array{lat: float, lng: float}|null */
    private static function extract(string $text): ?array
    {
        foreach ([$text, urldecode($text)] as $candidate) {
            // Ordered by how precisely each names the pin: !3d/!4d is the
            // place itself, q/ll a queried point, @ only the viewport center.
            $patterns = [
                '/!3d(-?\d+(?:\.\d+)?)!4d(-?\d+(?:\.\d+)?)/',
                '/[?&](?:q|query|ll|destination|center)=\(?(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)/i',
                '/@(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)/',
                '/^\(?\s*(-?\d+(?:\.\d+)?)\s*[,;]\s*(-?\d+(?:\.\d+)?)\s*\)?$/',
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $candidate, $m)
                    && abs((float) $m[1]) <= 90
                    && abs((float) $m[2]) <= 180
                ) {
                    return ['lat' => (float) $m[1], 'lng' => (float) $m[2]];
                }
            }
        }

        return null;
    }

    /** @return array{lat: float, lng: float}|null */
    private static function resolveShortLink(string $input): ?array
    {
        $host = parse_url($input, PHP_URL_HOST);
        if (! in_array($host, self::SHORT_LINK_HOSTS, true)) {
            return null;
        }

        try {
            $url = $input;
            // Follow hops by hand so every intermediate URL gets parsed —
            // the coordinates live in the redirect target, not the body.
            for ($hop = 0; $hop < 5; $hop++) {
                $next = Http::timeout(6)
                    ->withOptions(['allow_redirects' => false])
                    ->get($url)
                    ->header('Location');
                if (! $next) {
                    return null;
                }
                if ($coords = self::extract($next)) {
                    return $coords;
                }
                $url = $next;
            }
        } catch (\Throwable) {
            // Network failure just means "couldn't read the link".
        }

        return null;
    }
}
