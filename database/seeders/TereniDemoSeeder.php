<?php

namespace Database\Seeders;

use App\Enums\CourtAccess;
use App\Enums\CourtType;
use App\Models\Court;
use App\Models\Facility;
use App\Models\Steward;
use Illuminate\Database\Seeder;

/**
 * A single pilot object for the "Tereni Medijana" MVP so the map and admin
 * aren't empty on a fresh install. Idempotent — safe to run repeatedly.
 *
 *   php artisan db:seed --class=Database\\Seeders\\TereniDemoSeeder
 */
class TereniDemoSeeder extends Seeder
{
    public function run(): void
    {
        $facility = Facility::firstOrCreate(
            ['slug' => 'os-kralj-petar-i'],
            [
                'name' => 'OŠ "Kralj Petar I"',
                'ownership' => 'skola',
                'address' => 'Vojvode Putnika, Medijana, Niš',
                'lat' => 43.3205,
                'lng' => 21.9142,
            ],
        );

        Steward::firstOrCreate(
            ['facility_id' => $facility->id, 'name' => 'Domar objekta'],
            [
                'role' => 'domar',
                'email' => 'domar@example.test',
                'phone' => '+381600000000',
                'notify' => true,
            ],
        );

        Court::firstOrCreate(
            ['slug' => 'kralj-petar-kosarka'],
            [
                'facility_id' => $facility->id,
                'name' => 'Košarkaški teren',
                'type' => CourtType::Basketball,
                'surface' => 'beton',
                'dimensions' => '28 × 15 m',
                'has_lighting' => true,
                'access' => CourtAccess::Public,
                'description' => 'Otvoreni košarkaški teren u dvorištu škole, dostupan van nastave.',
                'lat' => 43.3206,
                'lng' => 21.9145,
                'is_active' => true,
            ],
        );

        Court::firstOrCreate(
            ['slug' => 'kralj-petar-mali-fudbal'],
            [
                'facility_id' => $facility->id,
                'name' => 'Teren za mali fudbal',
                'type' => CourtType::Football,
                'surface' => 'veštačka trava',
                'dimensions' => '40 × 20 m',
                'has_lighting' => false,
                'access' => CourtAccess::StudentsOnly,
                'lat' => 43.3203,
                'lng' => 21.9139,
                'is_active' => true,
            ],
        );
    }
}
