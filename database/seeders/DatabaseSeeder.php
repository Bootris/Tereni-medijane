<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'borisboncic95@gmail.com'],
            [
                'name' => 'Boris Boncic',
                'password' => env('SEED_ADMIN_PASSWORD', 'password'),
                'role' => User::ROLE_ADMIN,
            ],
        );

        foreach ([
            'Privredno pravo',
            'Građansko pravo',
            'Krivično pravo',
            'Porodično pravo',
            'Radno pravo',
        ] as $name) {
            Category::firstOrCreate(['name' => $name]);
        }

        $defaults = [
            'site_name' => 'Advokatska kancelarija',
            'email' => 'info@example.com',
            'phone' => '+381 11 000 0000',
            'address' => 'Ulica i broj, 11000 Beograd',
            'working_hours' => 'Pon–Pet 09:00–17:00',
        ];

        foreach ($defaults as $key => $value) {
            if (Setting::query()->where('key', $key)->doesntExist()) {
                Setting::set($key, $value);
            }
        }
    }
}
