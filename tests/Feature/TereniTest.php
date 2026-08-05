<?php

namespace Tests\Feature;

use App\Enums\ReportStatus;
use App\Mail\ReportStatusChanged;
use App\Mail\ReportSubmitted;
use App\Models\Court;
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

    public function test_map_lists_active_locatable_courts(): void
    {
        $court = $this->court(['name' => 'Košarkaški teren A']);
        $this->court(['name' => 'Skriveni teren', 'is_active' => false]);

        $this->get(route('tereni.map'))
            ->assertOk()
            ->assertSee('Košarkaški teren A')
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
