<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "Tereni Medijana" - public map + QR reporting for sports fields.
 *
 * facility → court → report → report_status_change
 *                 ↘ steward (responsible person, notified on new reports)
 *
 * See docs/TERENI.md.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Objekat - a school yard or open sports ground.
        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('ownership')->nullable();       // škola / opština / privatno
            $table->string('address')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Teren - an individual field within a facility. The QR slug points here.
        Schema::create('courts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();              // QR slug - public URL segment
            $table->string('type');                        // CourtType
            $table->string('surface')->nullable();         // beton, tartan, veštačka trava…
            $table->string('dimensions')->nullable();      // "28 × 15 m"
            $table->boolean('has_lighting')->default(false);
            $table->string('access')->default('javno');    // CourtAccess
            $table->text('description')->nullable();
            $table->json('gallery')->nullable();           // array of stored image paths
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Zaduženo lice - notified (email + SMS) when a report lands on their facility.
        Schema::create('stewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('role')->nullable();            // domar, nastavnik, JKP…
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->boolean('notify')->default(true);
            $table->timestamps();
        });

        // Prijava - a citizen report. Held for moderation (is_public=false) until
        // an editor publishes it, so the public timeline can't be spammed.
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('court_id')->constrained()->cascadeOnDelete();
            $table->string('category');                    // ReportCategory
            $table->text('description')->nullable();
            $table->string('photo');                       // required - proof, anti-spam
            $table->string('status')->default('prijavljeno'); // ReportStatus
            $table->string('reporter_name')->nullable();
            $table->string('reporter_contact')->nullable(); // optional phone/email for follow-up
            $table->string('reporter_ip', 45)->nullable();
            $table->boolean('is_public')->default(false);  // moderation gate
            $table->boolean('is_flagged')->default(false); // citizen "flag" on a public report
            $table->text('resolution_note')->nullable();   // public comment from the steward
            $table->timestamps();
            $table->index(['court_id', 'is_public', 'status']);
        });

        // Istorija promena - one row per status transition, drives the public timeline.
        Schema::create('report_status_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->cascadeOnDelete();
            $table->string('status');                      // ReportStatus
            $table->text('note')->nullable();              // public explanation for this step
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_status_changes');
        Schema::dropIfExists('reports');
        Schema::dropIfExists('stewards');
        Schema::dropIfExists('courts');
        Schema::dropIfExists('facilities');
    }
};
