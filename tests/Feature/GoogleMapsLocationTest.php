<?php

namespace Tests\Feature;

use App\Support\Tereni\GoogleMapsLocation;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleMapsLocationTest extends TestCase
{
    public function test_parses_bare_coordinates(): void
    {
        $this->assertSame(
            ['lat' => 43.3209234, 'lng' => 21.9033456],
            GoogleMapsLocation::parse('43.3209234, 21.9033456')
        );
        $this->assertSame(
            ['lat' => 43.3209, 'lng' => 21.9033],
            GoogleMapsLocation::parse('(43.3209, 21.9033)')
        );
    }

    public function test_parses_viewport_url(): void
    {
        $this->assertSame(
            ['lat' => 43.3209234, 'lng' => 21.9033456],
            GoogleMapsLocation::parse('https://www.google.com/maps/@43.3209234,21.9033456,17z')
        );
    }

    public function test_place_pin_beats_viewport_center(): void
    {
        $url = 'https://www.google.com/maps/place/O%C5%A0+Vo%C5%BEd/@43.31,21.89,17z/data=!4m6!3m5!8m2!3d43.3209234!4d21.9033456';

        $this->assertSame(
            ['lat' => 43.3209234, 'lng' => 21.9033456],
            GoogleMapsLocation::parse($url)
        );
    }

    public function test_parses_query_url(): void
    {
        $this->assertSame(
            ['lat' => 43.3209, 'lng' => 21.9033],
            GoogleMapsLocation::parse('https://maps.google.com/?q=43.3209,21.9033')
        );
    }

    public function test_resolves_short_link_via_redirect(): void
    {
        Http::fake([
            'maps.app.goo.gl/*' => Http::response('', 302, [
                'Location' => 'https://www.google.com/maps/place/X/@43.31,21.89,17z/data=!3d43.3209234!4d21.9033456',
            ]),
        ]);

        $this->assertSame(
            ['lat' => 43.3209234, 'lng' => 21.9033456],
            GoogleMapsLocation::parse('https://maps.app.goo.gl/AbCdEf123')
        );
    }

    public function test_resolves_consent_redirect_with_encoded_target(): void
    {
        Http::fake([
            'maps.app.goo.gl/*' => Http::response('', 302, [
                'Location' => 'https://consent.google.com/m?continue=https%3A%2F%2Fwww.google.com%2Fmaps%2F%4043.3209234%2C21.9033456%2C17z',
            ]),
        ]);

        $this->assertSame(
            ['lat' => 43.3209234, 'lng' => 21.9033456],
            GoogleMapsLocation::parse('https://maps.app.goo.gl/AbCdEf123')
        );
    }

    public function test_share_text_with_place_name_above_the_link(): void
    {
        Http::fake([
            'maps.app.goo.gl/*' => Http::response('', 302, [
                'Location' => 'https://www.google.com/maps/place/Kocka/@43.31,21.89,17z/data=!3d43.3209234!4d21.9033456',
            ]),
        ]);

        $this->assertSame(
            ['lat' => 43.3209234, 'lng' => 21.9033456],
            GoogleMapsLocation::parse("Kocka\nhttps://maps.app.goo.gl/AbCdEf123?g_st=ac")
        );
    }

    public function test_share_google_link_resolves_via_street_view_panorama(): void
    {
        // share.google → Google Search entity page (JS-only, no coordinates);
        // the crawler preview exposes the Street View pano in front of the place.
        Http::fake([
            'share.google/*' => Http::response('', 302, [
                'Location' => 'https://www.google.com/share.google?q=Wb6MNjlONGZicgZMv',
            ]),
            'www.google.com/share.google*' => Http::response('', 301, [
                'Location' => 'https://www.google.com/search?output=search&kgmid=/g/11z8m257ns&q=Kosarkaski+teren',
            ]),
            'www.google.com/search*' => Http::response(
                '<meta content="https://streetviewpixels-pa.googleapis.com/v1/thumbnail?panoid=aHxhHv_tQveWumePtXO3zQ&yaw=34.2" property="og:image">'
            ),
            'www.google.com/maps/photometa/*' => Http::response(
                ")]}'\n[[],[[[2],[[null,null,43.31745690249124,21.92143228387344],[196.7]],[[null,null,43.31750274118749,21.9214702042225]]]]]"
            ),
        ]);

        $this->assertSame(
            ['lat' => 43.3174569, 'lng' => 21.9214323, 'approx' => true],
            GoogleMapsLocation::parse("Kosarkaski teren\nhttps://share.google/Wb6MNjlONGZicgZMv")
        );
    }

    public function test_place_id_only_link_never_uses_page_viewport(): void
    {
        // The plain-HTTP Maps page centres its map on the *client's* IP, not
        // the place — those coordinates must never be mistaken for the pin.
        Http::fake([
            'maps.app.goo.gl/*' => Http::response('', 302, [
                'Location' => 'https://www.google.com/maps/place/Kocka/data=!4m2!3m1!1s0x4755b0b3a4:0x9f?entry=tts',
            ]),
            'www.google.com/*' => Http::response(
                '<meta content="https://maps.google.com/maps/api/staticmap?center=43.3147738%2C21.89862895&zoom=16" property="og:image">'
            ),
        ]);

        $this->assertNull(GoogleMapsLocation::parse('https://maps.app.goo.gl/AbCdEf123'));
    }

    public function test_parses_dropped_pin_search_url_and_dms(): void
    {
        $this->assertSame(
            ['lat' => 43.3209, 'lng' => 21.9033],
            GoogleMapsLocation::parse('https://www.google.com/maps/search/43.3209,+21.9033?entry=tts')
        );
        $this->assertSame(
            ['lat' => 43.3208889, 'lng' => 21.9033056],
            GoogleMapsLocation::parse('43°19\'15.2"N 21°54\'11.9"E')
        );
    }

    public function test_never_follows_redirect_off_google(): void
    {
        Http::fake([
            'maps.app.goo.gl/*' => Http::response('', 302, ['Location' => 'http://169.254.169.254/latest']),
        ]);

        $this->assertNull(GoogleMapsLocation::parse('https://maps.app.goo.gl/AbCdEf123'));
        Http::assertNotSent(fn ($request) => str_contains($request->url(), '169.254.169.254'));
    }

    public function test_rejects_garbage_and_out_of_range(): void
    {
        Http::fake();

        $this->assertNull(GoogleMapsLocation::parse(''));
        $this->assertNull(GoogleMapsLocation::parse('OŠ Vožd Karađorđe'));
        $this->assertNull(GoogleMapsLocation::parse('999, 21.9'));

        // Non-maps hosts are never fetched.
        Http::assertNothingSent();
    }
}
