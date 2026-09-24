<?php

namespace Database\Seeders;

use App\Enums\CourtAccess;
use App\Enums\CourtType;
use App\Models\Court;
use App\Models\Facility;
use App\Models\Steward;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Pilot data for the "Tereni Medijana" MVP so the map, the field pages and the
 * admin aren't empty on a fresh install: three locations from the field survey
 * (docs/TERENI.md, phase 1) with the photos taken on site - a school yard, an
 * asphalt court between apartment blocks, and the courts in the park.
 *
 * Photos live in database/seeders/photos/tereni/ and are copied onto the public
 * disk under tereni/courts/ - the same directory the admin uploads to, and the
 * only one Court::deleteGalleryFiles() is allowed to clean up.
 *
 * Names, addresses and coordinates are placeholders until the survey confirms
 * them per location; the photos are the real thing.
 *
 * Idempotent: re-running never duplicates a row, never re-copies a photo, and
 * never overwrites a gallery an editor has already curated.
 *
 *   php artisan db:seed --class=Database\\Seeders\\TereniDemoSeeder
 */
class TereniDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->schoolYard();
        $this->blockCourt();
        $this->parkCourts();
    }

    /** Školsko dvorište: asfaltna košarka, teren sa veštačkom travom, nova modularna odbojka. */
    private function schoolYard(): void
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

        $this->steward($facility, 'Domar objekta', 'domar', 'domar@example.test', '+381600000000');

        $this->court($facility, 'kralj-petar-kosarka', [
            'name' => 'Košarkaški teren',
            'type' => CourtType::Basketball,
            'surface' => 'asfalt',
            'dimensions' => '28 × 15 m',
            'has_lighting' => true,
            'access' => CourtAccess::Public,
            'description' => 'Otvoreni košarkaški teren u dvorištu škole, dostupan van nastave. '
                .'Asfalt je ispucao, table i obruči su dotrajali.',
            'lat' => 43.3206,
            'lng' => 21.9145,
        ], [
            'skola-kosarka-1.jpg',
            'skola-kosarka-2.jpg',
            'skola-kosarka-3.jpg',
            'skola-kosarka-4.jpg',
        ]);

        $this->court($facility, 'kralj-petar-mali-fudbal', [
            'name' => 'Teren za mali fudbal',
            'type' => CourtType::Football,
            'surface' => 'veštačka trava',
            'dimensions' => '40 × 20 m',
            'has_lighting' => false,
            'access' => CourtAccess::StudentsOnly,
            'description' => 'Ograđen teren sa veštačkom travom i rukometnim golovima, uz fiskulturnu salu.',
            'lat' => 43.3203,
            'lng' => 21.9139,
        ], [
            'skola-mali-fudbal-1.jpg',
            'skola-mali-fudbal-2.jpg',
        ]);

        $this->court($facility, 'kralj-petar-odbojka', [
            'name' => 'Odbojkaški teren (modularna podloga)',
            'type' => CourtType::Volleyball,
            'surface' => 'modularne plastične ploče',
            'dimensions' => '18 × 9 m',
            'has_lighting' => false,
            'access' => CourtAccess::Public,
            'description' => 'Novopostavljena modularna podloga preko asfalta, sa stubovima za mrežu.',
            'lat' => 43.3208,
            'lng' => 21.9147,
        ], [
            'skola-odbojka-1.jpg',
        ]);
    }

    /** Asfaltni teren između zgrada - mreža stoji, linije izbledele. */
    private function blockCourt(): void
    {
        $facility = Facility::firstOrCreate(
            ['slug' => 'otvoreni-teren-u-bloku'],
            [
                'name' => 'Otvoreni teren u bloku',
                'ownership' => 'opstina',
                'address' => 'Medijana, Niš',
                'notes' => 'Teren u okviru stambenog bloka; nadležnost potvrditi u opštini.',
                'lat' => 43.3181,
                'lng' => 21.9187,
            ],
        );

        $this->steward($facility, 'Komunalna služba', 'komunalno', 'komunalno@example.test', '+381600000001');

        $this->court($facility, 'blok-odbojka', [
            'name' => 'Odbojkaški teren u bloku',
            'type' => CourtType::Volleyball,
            'surface' => 'asfalt',
            'dimensions' => '18 × 9 m',
            'has_lighting' => false,
            'access' => CourtAccess::Public,
            'description' => 'Asfaltni teren između zgrada. Mreža je postavljena, ali su linije izbledele '
                .'i podloga je ispucala.',
            'lat' => 43.3182,
            'lng' => 21.9188,
        ], [
            'blok-odbojka-1.jpg',
            'blok-odbojka-2.jpg',
        ]);
    }

    /** Park: stara asfaltna košarka i novoizgrađen ograđen teren za mali fudbal. */
    private function parkCourts(): void
    {
        $facility = Facility::firstOrCreate(
            ['slug' => 'park-sportski-tereni'],
            [
                'name' => 'Sportski tereni u parku',
                'ownership' => 'opstina',
                'address' => 'Medijana, Niš',
                'lat' => 43.3168,
                'lng' => 21.9203,
            ],
        );

        $this->steward($facility, 'Održavanje parka', 'održavanje', 'park@example.test', '+381600000002');

        $this->court($facility, 'park-kosarka', [
            'name' => 'Košarkaški teren u parku',
            'type' => CourtType::Basketball,
            'surface' => 'asfalt',
            'dimensions' => '26 × 14 m',
            'has_lighting' => false,
            'access' => CourtAccess::Public,
            'description' => 'Stari asfaltni teren sa dve konstrukcije. Podloga je ispucala i zarasla po ivicama, '
                .'a jedna konstrukcija je iskrivljena i olabavljena u temelju.',
            'lat' => 43.3169,
            'lng' => 21.9205,
        ], [
            'park-kosarka-1.jpg',
            'park-kosarka-2.jpg',
            'park-kosarka-3.jpg',
            'park-kosarka-4.jpg',
        ]);

        $this->court($facility, 'park-mali-fudbal', [
            'name' => 'Teren za mali fudbal u parku',
            'type' => CourtType::Football,
            'surface' => 'veštačka trava',
            'dimensions' => '30 × 15 m',
            'has_lighting' => true,
            'access' => CourtAccess::Public,
            'description' => 'Novoizgrađen ograđen teren sa veštačkom travom, drvenim mantinelama i reflektorom.',
            'lat' => 43.3166,
            'lng' => 21.9201,
        ], [
            'park-mali-fudbal-1.jpg',
            'park-mali-fudbal-2.jpg',
        ]);
    }

    private function steward(Facility $facility, string $name, string $role, string $email, string $phone): void
    {
        Steward::firstOrCreate(
            ['facility_id' => $facility->id, 'name' => $name],
            [
                'role' => $role,
                'email' => $email,
                'phone' => $phone,
                'notify' => true,
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  list<string>  $photos  file names inside database/seeders/photos/tereni/
     */
    private function court(Facility $facility, string $slug, array $attributes, array $photos = []): void
    {
        $court = Court::firstOrCreate(
            ['slug' => $slug],
            $attributes + [
                'facility_id' => $facility->id,
                'is_active' => true,
            ],
        );

        // Backfill fields seeded before the survey photos existed, but never
        // clobber a gallery an editor has already curated in the admin.
        if (blank($court->gallery)) {
            $gallery = $this->storePhotos($slug, $photos);

            if ($gallery !== []) {
                $court->update(['gallery' => $gallery]);
            }
        }
    }

    /**
     * Copy survey photos onto the public disk and return their stored paths.
     * File names are derived from the field slug, so re-seeding reuses the
     * existing copies instead of piling up duplicates.
     *
     * @param  list<string>  $photos
     * @return list<string>
     */
    private function storePhotos(string $slug, array $photos): array
    {
        $stored = [];

        foreach ($photos as $i => $photo) {
            $source = __DIR__.'/photos/tereni/'.$photo;

            if (! is_file($source)) {
                continue;
            }

            $path = sprintf('tereni/courts/seed-%s-%d.jpg', $slug, $i + 1);

            if (! Storage::disk('public')->exists($path)) {
                Storage::disk('public')->put($path, file_get_contents($source));
            }

            $stored[] = $path;
        }

        return $stored;
    }
}
