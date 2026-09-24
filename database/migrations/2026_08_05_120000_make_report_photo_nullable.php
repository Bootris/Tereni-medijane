<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A citizen photo stays mandatory at submission (anti-spam validation), but
 * moderation must be able to REMOVE an inappropriate photo without deleting
 * the whole report - so the column itself becomes nullable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->string('photo')->nullable()->change();
        });
    }

    public function down(): void
    {
        // SQLite rebuilds the table on change(); NULL photos would fail the
        // NOT NULL constraint mid-rebuild and strand a __temp__ table.
        DB::table('reports')->whereNull('photo')->update(['photo' => '']);

        Schema::table('reports', function (Blueprint $table) {
            $table->string('photo')->nullable(false)->change();
        });
    }
};
