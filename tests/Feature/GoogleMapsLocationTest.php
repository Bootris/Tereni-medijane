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
