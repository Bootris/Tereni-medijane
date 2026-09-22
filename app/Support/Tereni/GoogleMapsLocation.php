<?php

namespace App\Support\Tereni;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Pulls coordinates out of whatever gets pasted from Google Maps:
 * bare coordinates ("43.3209, 21.9033" or 43°19'15.2"N 21°54'11.9"E), a full
 * maps URL (@lat,lng / !3dlat!4dlng / ?q=lat,lng / /search/lat,+lng), or a
 * maps.app.goo.gl share link — possibly with the place name pasted above it.
 * Links whose URL carries no coordinates (place-id only) are fetched and the
 * point is read from the page itself.
 */
class GoogleMapsLocation
{
    private const SHORT_LINK_HOSTS = ['maps.app.goo.gl', 'goo.gl', 'g.co'];

    /** Sent on every fetch: a browser UA plus a pre-accepted EU consent cookie. */
    private const HEADERS = [
        'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0 Safari/537.36',
        'Accept-Language' => 'sr,en;q=0.8',
        'Cookie' => 'SOCS=CAESEwgDEgk0ODE3Nzk3MjQaAmVuIAEaBgiA_LyaBg; CONSENT=YES+',
    ];

    /** @return array{lat: float, lng: float}|null */
    public static function parse(?string $input): ?array
    {
        $input = trim((string) $input);
        if ($input === '') {
            return null;
        }

        if ($coords = self::extract($input)) {
            return $coords;
        }

        // Share sheets paste "Place name\nhttps://…" — take the link itself.
        if (preg_match('~https?://\S+~i', $input, $m)) {
            return self::extract($m[0]) ?? self::resolve($m[0]);
        }

        return null;
    }

    /** @return array{lat: float, lng: float}|null */
    private static function extract(string $text): ?array
    {
        foreach ([$text, urldecode($text)] as $candidate) {
            // Ordered by how precisely each names the pin: !3d/!4d is the
            // place itself, q/ll and /search/ a queried point, @ only the
            // viewport center.
            $patterns = [
                '/!3d(-?\d+(?:\.\d+)?)!4d(-?\d+(?:\.\d+)?)/',
                '/[?&](?:q|query|ll|destination|center)=\(?(-?\d+(?:\.\d+)?)\s*(?:,|%2C)[\s+]*(-?\d+(?:\.\d+)?)/i',
                '~/maps/(?:search|place|dir)/(-?\d+\.\d+)\s*,[\s+]*(-?\d+\.\d+)~i',
                '/@(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)/',
                '/^\(?\s*(-?\d+(?:\.\d+)?)\s*[,;]\s*(-?\d+(?:\.\d+)?)\s*\)?$/',
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $candidate, $m) && ($coords = self::valid($m[1], $m[2]))) {
                    return $coords;
                }
            }

            if ($coords = self::extractDms($candidate)) {
                return $coords;
            }
        }

        return null;
    }

    /** 43°19'15.2"N 21°54'11.9"E — what Google shows for a dropped pin. */
    private static function extractDms(string $text): ?array
    {
        $part = '(\d{1,3})°\s*(\d{1,2})[\'′]\s*(\d{1,2}(?:\.\d+)?)["″]?\s*([NSEW])';
        if (! preg_match("/{$part}[\\s,+]*{$part}/iu", $text, $m)) {
            return null;
        }

        $toDecimal = fn ($d, $min, $s, $h) => ((float) $d + (float) $min / 60 + (float) $s / 3600)
            * (in_array(strtoupper($h), ['S', 'W'], true) ? -1 : 1);

        $a = $toDecimal($m[1], $m[2], $m[3], $m[4]);
        $b = $toDecimal($m[5], $m[6], $m[7], $m[8]);
        [$lat, $lng] = in_array(strtoupper($m[4]), ['N', 'S'], true) ? [$a, $b] : [$b, $a];

        return self::valid(round($lat, 7), round($lng, 7));
    }

    /** @return array{lat: float, lng: float}|null */
    private static function valid($lat, $lng): ?array
    {
        $lat = (float) $lat;
        $lng = (float) $lng;

        return abs($lat) <= 90 && abs($lng) <= 180 ? ['lat' => $lat, 'lng' => $lng] : null;
    }

    /**
     * Follow a Google link hop by hop (every intermediate URL is parsed — the
     * coordinates usually sit in a redirect target), then read the final page:
     * its preview image is centered on the place ("center=lat%2Clng").
     *
     * @return array{lat: float, lng: float}|null
     */
    private static function resolve(string $url): ?array
    {
        if (! self::isGoogleHost(parse_url($url, PHP_URL_HOST))) {
            return null;
        }

        // Every hop is recorded so a failure in production (where Google
        // treats datacenter IPs differently) can be read from the log.
        $trace = [];
        $error = null;

        try {
            for ($hop = 0; $hop < 6; $hop++) {
                $response = Http::timeout(8)
                    ->withHeaders(self::HEADERS)
                    ->withOptions(['allow_redirects' => false])
                    ->get($url);

                $next = $response->header('Location');
                $trace[] = $response->status() . ' ' . $url . ($next ? ' -> ' . $next : '');

                if (! $next) {
                    if ($coords = self::extractFromPage($response->body())) {
                        return $coords;
                    }
                    $trace[] = 'page without coordinates: ' . mb_substr(strip_tags($response->body()), 0, 200);
                    break;
                }

                if (str_starts_with($next, '/')) {
                    $next = parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST) . $next;
                }
                if ($coords = self::extract($next)) {
                    return $coords;
                }

                // Consent interstitial: skip straight to the page it guards.
                parse_str((string) parse_url($next, PHP_URL_QUERY), $query);
                if (! empty($query['continue']) && is_string($query['continue'])) {
                    $next = $query['continue'];
                }

                // Never follow a redirect off Google (no SSRF via crafted links).
                if (! self::isGoogleHost(parse_url($next, PHP_URL_HOST))) {
                    $trace[] = 'refused non-Google redirect';
                    break;
                }
                $url = $next;
            }
        } catch (\Throwable $e) {
            // Network failure just means "couldn't read the link".
            $error = $e->getMessage();
        }

        Log::warning('Google Maps link: lokacija nije pročitana', [
            'hops' => $trace,
            'error' => $error,
        ]);

        return null;
    }

    /** @return array{lat: float, lng: float}|null */
    private static function extractFromPage(string $html): ?array
    {
        if (preg_match('/center=(-?\d+\.\d+)(?:%2C|,)(-?\d+\.\d+)/i', $html, $m)) {
            return self::valid($m[1], $m[2]);
        }
        // APP_INITIALIZATION_STATE=[[[zoom, lng, lat] — note the lng-first order.
        if (preg_match('/APP_INITIALIZATION_STATE=\[\[\[[-\d.]+,(-?\d+\.\d+),(-?\d+\.\d+)\]/', $html, $m)) {
            return self::valid($m[2], $m[1]);
        }

        return null;
    }

    private static function isGoogleHost(?string $host): bool
    {
        $host = strtolower((string) $host);

        return in_array($host, self::SHORT_LINK_HOSTS, true)
            || (bool) preg_match('/(^|\.)google\.(com|[a-z]{2}|co\.[a-z]{2}|com\.[a-z]{2})$/', $host);
    }
}
