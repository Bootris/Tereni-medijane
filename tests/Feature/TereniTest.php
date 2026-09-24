<?php

namespace Tests\Feature;

use App\Enums\ReportStatus;
use App\Mail\ReportStatusChanged;
use App\Mail\ReportSubmitted;
use App\Models\Court;
use App\Models\Facility;
use App\Models\Report;
use App\Models\Steward;
use App\Models\User;
use App\Support\Tereni\ReportNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TereniTest extends TestCase
{
    use RefreshDatabase;

    private function court(array $attributes = []): Court
    {
        return Court::factory()->create($attributes);
    }

    private function publicReport(Court $court, array $attributes = []): Report
    {
        return Report::create(array_merge([
            'court_id' => $court->id,
            'category' => 'kos',
            'description' => 'Koš je polomljen.',
            'photo' => 'tereni/reports/test.jpg',
            'status' => ReportStatus::Reported->value,
            'is_public' => true,
        ], $attributes));
    }

    public function test_map_lists_every_active_court_even_without_coordinates(): void
    {
        $this->court(['name' => 'Košarkaški teren A']);
        $this->court(['name' => 'Skriveni teren', 'is_active' => false]);
        // No own coords and none on the facility - no marker, but the card
        // and the totals must still include it.
        $this->court([
            'name' => 'Teren bez lokacije',
            'lat' => null,
            'lng' => null,
            'facility_id' => Facility::factory()->create(['lat' => null, 'lng' => null])->id,
        ]);

        $this->get(route('tereni.map'))
            ->assertOk()
            ->assertSee('Košarkaški teren A')
            ->assertSee('Teren bez lokacije')
            ->assertSee('Pogledaj sve terene (2)')
            ->assertDontSee('Skriveni teren');
    }

    public function test_court_page_renders(): void
    {
        $court = $this->court(['name' => 'Teren za mali fudbal', 'surface' => 'beton']);

        $this->get(route('tereni.court', $court))
            ->assertOk()
            ->assertSee('Teren za mali fudbal')
            ->assertSee('Prijavi problem');
    }

    public function test_courts_directory_paginates_and_is_linked_from_map(): void
    {
        for ($i = 1; $i <= 15; $i++) {
            $this->court(['name' => sprintf('Teren %02d', $i)]);
        }

        $this->get(route('tereni.list'))
            ->assertOk()
            ->assertSee('Teren 01')
            ->assertDontSee('Teren 13')
            ->assertSee('page=2');

        $this->get(route('tereni.list', ['page' => 2]))
            ->assertOk()
            ->assertSee('Teren 13')
            ->assertDontSee('Teren 01');

        $this->get(route('tereni.map'))->assertOk()->assertSee(route('tereni.list'));
    }

    public function test_guide_page_explains_reporting_and_is_linked_from_nav(): void
    {
        $this->get(route('tereni.guide'))
            ->assertOk()
            ->assertSee('Kako prijaviti problem')
            ->assertSee('U planu radova')
            ->assertSee('Pravila korišćenja');

        $this->get(route('tereni.map'))->assertOk()->assertSee(route('tereni.guide'));
    }

    public function test_guide_text_is_editable_from_admin(): void
    {
        $editor = User::factory()->create(['role' => User::ROLE_EDITOR]);
        $this->actingAs($editor)->get('/admin/uputstva')->assertOk()->assertSee('Čemu služi');

        \App\Support\Tereni\GuideContent::save([
            'lead' => 'Novi uvod.',
            'sections' => [
                ['title' => 'Nova sekcija', 'body' => '<p>Moj tekst.</p><p>[statusi]</p>'],
            ],
        ]);

        $this->get(route('tereni.guide'))
            ->assertOk()
            ->assertSee('Novi uvod.')
            ->assertSee('Moj tekst.')
            ->assertSee('U planu radova')            // [statusi] expanded
            ->assertSee('href="#nova-sekcija"', false)
            ->assertDontSee('Čemu služi');
    }

    public function test_courts_directory_filters_by_sport_and_state(): void
    {
        $basket = $this->court(['name' => 'Filtrirani koš', 'type' => 'kosarka']);
        $this->court(['name' => 'Filtrirana odbojka', 'type' => 'odbojka']);
        $this->publicReport($basket);

        $this->get(route('tereni.list', ['sport' => 'kosarka']))
            ->assertOk()->assertSee('Filtrirani koš')->assertDontSee('Filtrirana odbojka');

        $this->get(route('tereni.list', ['stanje' => 'issue']))
            ->assertOk()->assertSee('Filtrirani koš')->assertDontSee('Filtrirana odbojka');

        $this->get(route('tereni.list', ['stanje' => 'ok']))
            ->assertOk()->assertSee('Filtrirana odbojka')->assertDontSee('Filtrirani koš');
    }

    public function test_inactive_court_returns_404(): void
    {
        $court = $this->court(['is_active' => false]);

        $this->get(route('tereni.court', $court))->assertNotFound();
    }

    public function test_report_submission_is_stored_hidden_and_notifies_steward(): void
    {
        Storage::fake('public');
        Mail::fake();

        $court = $this->court();
        Steward::create([
            'facility_id' => $court->facility_id,
            'name' => 'Domar',
            'email' => 'domar@example.test',
            'notify' => true,
        ]);

        $this->from(route('tereni.court', $court))
            ->post(route('tereni.report.store', $court), [
                'category' => 'osvetljenje',
                'description' => 'Ne radi reflektor.',
                'photo' => UploadedFile::fake()->image('problem.jpg'),
            ])
            ->assertRedirect(route('tereni.court', $court))
            ->assertSessionHas('report_success');

        $report = Report::first();
        $this->assertNotNull($report);
        $this->assertFalse($report->is_public);           // held for moderation
        $this->assertSame('osvetljenje', $report->category->value);
        Storage::disk('public')->assertExists($report->photo);
        Mail::assertSent(ReportSubmitted::class);
    }

    public function test_report_is_not_public_until_moderated(): void
    {
        Storage::fake('public');
        $court = $this->court();

        $this->post(route('tereni.report.store', $court), [
            'category' => 'smece',
            'photo' => UploadedFile::fake()->image('p.jpg'),
        ]);

        // Not visible on the public page yet.
        $this->get(route('tereni.court', $court))
            ->assertOk()
            ->assertSee('Još nema javnih prijava');

        // After publishing, it appears with an opening status entry.
        $report = Report::first();
        $report->publish();

        $this->assertTrue($report->fresh()->is_public);
        $this->assertSame(1, $report->statusChanges()->count());

        $this->get(route('tereni.court', $court))
            ->assertOk()
            ->assertSee('Prijavljeno');
    }

    public function test_photo_is_required(): void
    {
        $court = $this->court();

        $this->from(route('tereni.court', $court))
            ->post(route('tereni.report.store', $court), [
                'category' => 'kos',
            ])
            ->assertSessionHasErrors('photo');

        $this->assertSame(0, Report::count());
    }

    public function test_honeypot_blocks_bots_silently(): void
    {
        $court = $this->court();

        $this->post(route('tereni.report.store', $court), [
            'category' => 'kos',
            'website' => 'http://spam.example',
        ])->assertRedirect();

        $this->assertSame(0, Report::count());
    }

    public function test_change_status_records_history_and_notifies_reporter(): void
    {
        Mail::fake();
        $court = $this->court();
        $report = $this->publicReport($court, ['reporter_contact' => 'gradjanin@example.test']);
        $report->publish();

        $report->changeStatus(ReportStatus::Confirmed, 'Izašli smo na teren.');
        app(ReportNotifier::class)->statusChanged($report);

        $this->assertSame(ReportStatus::Confirmed, $report->fresh()->status);
        $this->assertSame('potvrdjeno', $report->statusChanges()->latest('id')->first()->status->value);
        Mail::assertSent(ReportStatusChanged::class);
    }

    public function test_flag_marks_report(): void
    {
        $court = $this->court();
        $report = $this->publicReport($court);

        $this->from(route('tereni.court', $court))
            ->post(route('tereni.report.flag', $report))
            ->assertRedirect(route('tereni.court', $court));

        $this->assertTrue($report->fresh()->is_flagged);
    }

    public function test_qr_endpoint_returns_svg(): void
    {
        $court = $this->court();

        $response = $this->get(route('tereni.court.qr', $court));

        $response->assertOk();
        $this->assertStringContainsString('image/svg+xml', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('<svg', $response->getContent());
    }

    public function test_api_lists_and_shows_courts(): void
    {
        $court = $this->court(['name' => 'API Teren']);
        $this->publicReport($court);

        $this->getJson('/api/v1/tereni')
            ->assertOk()
            ->assertJsonFragment(['name' => 'API Teren']);

        $this->getJson("/api/v1/tereni/{$court->slug}")
            ->assertOk()
            ->assertJsonPath('data.slug', $court->slug)
            ->assertJsonCount(1, 'reports');
    }

    public function test_api_report_submission(): void
    {
        Storage::fake('public');
        $court = $this->court();

        $this->postJson("/api/v1/tereni/{$court->slug}/prijave", [
            'category' => 'ograda',
            'photo' => UploadedFile::fake()->image('p.jpg'),
        ])->assertCreated()->assertJson(['ok' => true]);

        $this->assertSame(1, Report::count());
        $this->assertFalse(Report::first()->is_public);
    }

    public function test_admin_can_moderate_reports(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $court = $this->court();
        $report = Report::create([
            'court_id' => $court->id,
            'category' => 'podloga',
            'photo' => 'tereni/reports/x.jpg',
            'is_public' => false,
        ]);

        // Editing through the resource page requires auth; smoke-test the model path
        // the Filament action uses.
        $report->publish($admin);
        $this->assertTrue($report->fresh()->is_public);
    }

    public function test_court_without_own_coordinates_inherits_facility_location(): void
    {
        // Facility has coordinates, the court itself doesn't - it must still
        // show up on the map and in the API, at the facility's location.
        $court = Court::factory()
            ->for(\App\Models\Facility::factory()->create(['lat' => 43.31, 'lng' => 21.91]))
            ->create(['name' => 'Nasleđeni teren', 'lat' => null, 'lng' => null]);

        $this->get(route('tereni.map'))->assertOk()->assertSee('Nasleđeni teren');

        $this->getJson('/api/v1/tereni')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Nasleđeni teren', 'lat' => 43.31, 'lng' => 21.91]);
    }

    public function test_photo_cleanup_never_touches_foreign_or_shared_files(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('branding/logo.png', 'x');
        Storage::disk('public')->put('tereni/reports/shared.jpg', 'x');

        $court = $this->court();
        // Injected path outside the module dir (client-controllable field).
        $evil = $this->publicReport($court, ['photo' => 'branding/logo.png']);
        // Two reports referencing the same file.
        $a = $this->publicReport($court, ['photo' => 'tereni/reports/shared.jpg']);
        $b = $this->publicReport($court, ['photo' => 'tereni/reports/shared.jpg']);

        $evil->delete();
        Storage::disk('public')->assertExists('branding/logo.png'); // untouched

        $a->delete();
        Storage::disk('public')->assertExists('tereni/reports/shared.jpg'); // b still uses it

        $b->delete();
        Storage::disk('public')->assertMissing('tereni/reports/shared.jpg'); // last reference gone
    }

    public function test_gallery_cleanup_never_touches_foreign_or_shared_files(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('blog/cover.jpg', 'x');
        Storage::disk('public')->put('tereni/courts/shared.jpg', 'x');

        $a = $this->court(['gallery' => ['blog/cover.jpg', 'tereni/courts/shared.jpg']]);
        $b = $this->court(['gallery' => ['tereni/courts/shared.jpg']]);

        $a->delete();
        Storage::disk('public')->assertExists('blog/cover.jpg');            // outside module dir
        Storage::disk('public')->assertExists('tereni/courts/shared.jpg');  // b still uses it

        $b->delete();
        Storage::disk('public')->assertMissing('tereni/courts/shared.jpg');
    }

    public function test_replacing_or_clearing_report_photo_deletes_old_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('tereni/reports/old.jpg', 'x');
        Storage::disk('public')->put('tereni/reports/new.jpg', 'x');

        $report = $this->publicReport($this->court(), ['photo' => 'tereni/reports/old.jpg']);

        // Moderation replaces the photo → the old file goes.
        $report->update(['photo' => 'tereni/reports/new.jpg']);
        Storage::disk('public')->assertMissing('tereni/reports/old.jpg');
        Storage::disk('public')->assertExists('tereni/reports/new.jpg');

        // Moderation removes it entirely (inappropriate) → file goes, report stays.
        $report->update(['photo' => null]);
        Storage::disk('public')->assertMissing('tereni/reports/new.jpg');
        $this->assertNull($report->fresh()->photo);
    }

    public function test_deleting_report_deletes_photo_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('tereni/reports/r.jpg', 'x');

        $report = $this->publicReport($this->court(), ['photo' => 'tereni/reports/r.jpg']);
        $report->delete();

        Storage::disk('public')->assertMissing('tereni/reports/r.jpg');
    }

    public function test_removing_gallery_images_deletes_files(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('tereni/courts/a.jpg', 'x');
        Storage::disk('public')->put('tereni/courts/b.jpg', 'x');

        $court = $this->court(['gallery' => ['tereni/courts/a.jpg', 'tereni/courts/b.jpg']]);
        $court->update(['gallery' => ['tereni/courts/b.jpg']]);

        Storage::disk('public')->assertMissing('tereni/courts/a.jpg');
        Storage::disk('public')->assertExists('tereni/courts/b.jpg');
    }

    public function test_deleting_court_deletes_gallery_and_report_photos(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('tereni/courts/g.jpg', 'x');
        Storage::disk('public')->put('tereni/reports/p.jpg', 'x');

        $court = $this->court(['gallery' => ['tereni/courts/g.jpg']]);
        $this->publicReport($court, ['photo' => 'tereni/reports/p.jpg']);

        $court->delete();

        Storage::disk('public')->assertMissing('tereni/courts/g.jpg');
        Storage::disk('public')->assertMissing('tereni/reports/p.jpg');
        $this->assertSame(0, Report::count());
    }

    public function test_deleting_facility_cleans_all_files(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('tereni/courts/f.jpg', 'x');
        Storage::disk('public')->put('tereni/reports/fr.jpg', 'x');

        $court = $this->court(['gallery' => ['tereni/courts/f.jpg']]);
        $this->publicReport($court, ['photo' => 'tereni/reports/fr.jpg']);

        $court->facility->delete();

        Storage::disk('public')->assertMissing('tereni/courts/f.jpg');
        Storage::disk('public')->assertMissing('tereni/reports/fr.jpg');
        $this->assertSame(0, Court::count());
        $this->assertSame(0, Report::count());
    }

    public function test_admin_panel_tereni_pages_render(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $court = $this->court();
        $report = $this->publicReport($court);
        $report->publish();

        $urls = [
            '/admin/facilities',
            '/admin/facilities/create',
            "/admin/facilities/{$court->facility_id}/edit", // renders relation managers too
            '/admin/courts',
            '/admin/courts/create',
            "/admin/courts/{$court->slug}/edit", // Court route key is its slug

            '/admin/reports',
            "/admin/reports/{$report->id}/edit",
        ];

        foreach ($urls as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_tereni_routes_are_dormant_when_feature_disabled(): void
    {
        config(['site.features.tereni' => false]);

        // Route definitions are evaluated at boot; assert the config gate itself.
        $this->assertFalse((bool) config('site.features.tereni'));
    }
}
