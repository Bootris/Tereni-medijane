<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_privacy_and_terms_render_with_the_operator(): void
    {
        $this->withoutVite();
        Setting::set('legal_name', 'Operator Test d.o.o.');
        Setting::set('legal_id', '987654321');
        // $site is shared once at boot; refresh it after the test wrote the settings.
        View::share('site', Setting::allCached());

        foreach (['/privatnost' => 'Politika privatnosti', '/uslovi' => 'Uslovi korišćenja'] as $url => $heading) {
            $this->get($url)->assertOk()->assertSee($heading)->assertSee('Operator Test d.o.o.')->assertSee('987654321');
        }
    }

    public function test_footer_and_report_form_link_to_the_legal_pages(): void
    {
        $this->withoutVite();

        $this->get('/mapa')->assertSee(route('tereni.privacy'), false)->assertSee(route('tereni.terms'), false);
    }
}
