<?php

namespace App\Support\Tereni;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Pulls coordinates out of whatever gets pasted from Google Maps:
 * bare coordinates ("43.3209, 21.9033" or 43°19'15.2"N 21°54'11.9"E), a full
 * maps URL (@lat,lng / !3dlat!4dlng / ?q=lat,lng / /search/lat,+lng), or a
 * share link (maps.app.goo.gl, share.google) — possibly with the place name
 * pasted above it.
 *
 * Share links are followed hop by hop, since Google puts the coordinates in a
 * redirect target when it puts them anywhere. A link that only names the
 * place (share.google from Google Search, place-id-only maps links) renders
 * its location with JavaScript alone — no plain HTTP page carries it. For
 * those, the link-preview page Google serves to crawlers exposes the Street
 * View panorama in front of the place, whose position is an approximate
 * (street-side) location; the result is flagged `approx`.
 */
class GoogleMapsLocation
{
    private const SHORT_LINK_HOSTS = ['maps.app.goo.gl', 'goo.gl', 'g.co', 'share.google'];

    /** A browser UA plus a pre-accepted EU consent cookie. */
    private const BROWSER_HEADERS = [
        'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0 Safari/537.36',
        'Accept-Language' => 'sr,en;q=0.8',
        'Cookie' => 'SOCS=CAESEwgDEgk0ODE3Nzk3MjQaAmVuIAEaBgiA_LyaBg; CONSENT=YES+',
    ];

    /** Link-preview crawler: gets the og:* page instead of the JS app shell. */
    private const CRAWLER_HEADERS = [
        'User-Agent' => 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)',
        'Accept-Language' => 'sr,en;q=0.8',
        'Cookie' => 'SOCS=CAESEwgDEgk0ODE3Nzk3MjQaAmVuIAEaBgiA_LyaBg; CONSENT=YES+',
    ];

    /** @return array{lat: float, lng: float, approx?: bool}|null */
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
    private static function valid(string|float $lat, string|float $lng): ?array
    {
        $lat = (float) $lat;
        $lng = (float) $lng;

        return abs($lat) <= 90 && abs($lng) <= 180 ? ['lat' => $lat, 'lng' => $lng] : null;
    }

    /** @return array{lat: float, lng: float, approx?: bool}|null */
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
            // 1. As a browser: coordinates usually sit in a redirect target.
            if ($coords = self::follow($url, self::BROWSER_HEADERS, $trace)['coords']) {
                return $coords;
            }

            // 2. As a link-preview crawler: the og:image is the Street View
            //    panorama in front of the place — its position is close enough
            //    to flag as approximate.
            $page = self::follow($url, self::CRAWLER_HEADERS, $trace)['body'] ?? '';
            if (preg_match('/panoid=([\w-]+)/', $page, $m) && ($coords = self::panoramaLocation($m[1]))) {
                return $coords + ['approx' => true];
            }
            $trace[] = 'no coordinates and no panorama in preview page';
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

    /**
     * Follow redirects by hand, parsing each Location for coordinates and
     * never leaving Google (no SSRF via a crafted link).
     *
     * @return array{coords: ?array, body: ?string}
     */
    private static function follow(string $url, array $headers, array &$trace): array
    {
        for ($hop = 0; $hop < 6; $hop++) {
            /** @var Response $response */
            $response = Http::timeout(8)
                ->withHeaders($headers)
                ->withOptions(['allow_redirects' => false])
                ->get($url);

            $next = $response->header('Location');
            $trace[] = $response->status() . ' ' . $url . ($next ? ' -> ' . $next : '');

            if (! $next) {
                return ['coords' => null, 'body' => $response->body()];
            }

            if (str_starts_with($next, '/')) {
                $next = parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST) . $next;
            }
            if ($coords = self::extract($next)) {
                return ['coords' => $coords, 'body' => null];
            }

            // Consent interstitial: skip straight to the page it guards.
            parse_str((string) parse_url($next, PHP_URL_QUERY), $query);
            if (! empty($query['continue']) && is_string($query['continue'])) {
                $next = $query['continue'];
            }

            if (! self::isGoogleHost(parse_url($next, PHP_URL_HOST))) {
                $trace[] = 'refused non-Google redirect';
                break;
            }
            $url = $next;
        }

        return ['coords' => null, 'body' => null];
    }

    /** Position of a Street View panorama, via the endpoint the Maps UI itself uses. */
    private static function panoramaLocation(string $panoid): ?array
    {
        $body = Http::timeout(8)
            ->withHeaders(self::BROWSER_HEADERS)
            ->get('https://www.google.com/maps/photometa/v1', [
                'authuser' => 0,
                'hl' => 'en',
                'gl' => 'us',
                'pb' => "!1m4!1smaps_sv.tactile!11m2!2m1!1b1!2m2!1sen!2sus!3m3!1m2!1e2!2s{$panoid}!4m6!1e1!1e2!1e3!1e4!1e5!1e6",
            ])
            ->body();

        // The panorama's own position is the first [null,null,lat,lng] tuple;
        // neighbouring panoramas follow it.
        return preg_match('/\[null,null,(-?\d+\.\d+),(-?\d+\.\d+)\]/', $body, $m)
            ? self::valid(round((float) $m[1], 7), round((float) $m[2], 7))
            : null;
    }

    private static function isGoogleHost(?string $host): bool
    {
        $host = strtolower((string) $host);

        return in_array($host, self::SHORT_LINK_HOSTS, true)
            || (bool) preg_match('/(^|\.)google\.(com|[a-z]{2}|co\.[a-z]{2}|com\.[a-z]{2})$/', $host)
            || (bool) preg_match('/(^|\.)google$/', $host);
    }
}
