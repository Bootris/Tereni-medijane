<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "Posle popravke" photo, uploaded by the steward/moderator when resolving a
 * report. The public field page shows it next to the citizen's original photo
 * as a before/after pair - a strong trust signal that work actually happened.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->string('resolution_photo')->nullable()->after('resolution_note');
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn('resolution_photo');
        });
    }
};
