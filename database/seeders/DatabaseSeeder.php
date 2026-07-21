<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed a fresh site from config/site.php — nothing here is client-specific,
     * so the same seeder bootstraps any business. Idempotent.
     */
    public function run(): void
    {
        $admin = config('site.admin');

        User::updateOrCreate(
            ['email' => $admin['email']],
            [
                'name' => $admin['name'],
                'password' => $admin['password'],   // hashed via User cast
                'role' => User::ROLE_ADMIN,
            ],
        );

        // Blog taxonomy from the chosen preset.
        $preset = config('site.preset', 'generic');
        foreach (config("site.presets.$preset.categories", []) as $name) {
            Category::firstOrCreate(['name' => $name]);
        }

        // Brand + identity defaults. Only fill what isn't set yet so re-seeding
        // never clobbers values the client edited in the admin.
        $defaults = [
            'site_name' => config('site.name'),
            'tagline' => config('site.tagline'),
            'theme_primary' => config('site.brand.primary'),
            'theme_accent' => config('site.brand.accent'),
            'theme_font' => config('site.brand.font'),
        ];

        foreach ($defaults as $key => $value) {
            if ($value !== null && $value !== '' && Setting::query()->where('key', $key)->doesntExist()) {
                Setting::set($key, $value);
            }
        }
    }
}
