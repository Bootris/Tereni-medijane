<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin panel path
    |--------------------------------------------------------------------------
    | URL segment where the Filament admin is mounted. Give each deployment a
    | random slug in production (e.g. admin-x7k2p9) via ADMIN_PATH.
    */

    'admin_path' => env('ADMIN_PATH', 'admin'),

    /*
    |--------------------------------------------------------------------------
    | Client profile
    |--------------------------------------------------------------------------
    | Identity defaults used when seeding a fresh site. After seeding, the live
    | values are edited in Admin → Site settings (the DB always wins over these).
    | The `php artisan site:new` wizard writes most of these for you.
    */

    'name' => env('SITE_NAME', 'My Site'),
    'tagline' => env('SITE_TAGLINE', ''),
    'locale' => env('APP_LOCALE', 'sr'),

    /*
    | Business preset — drives default blog categories and (later) which sections
    | a frontend renders. One of the keys under `presets` below.
    */
    'preset' => env('SITE_PRESET', 'generic'),

    /*
    | Brand defaults. Also editable per-site in Admin → Site settings.
    */
    'brand' => [
        'primary' => env('SITE_BRAND_PRIMARY', '#1a3d5c'),
        'accent' => env('SITE_BRAND_ACCENT', '#c9a24a'),
        'font' => env('SITE_BRAND_FONT', 'Inter'),
    ],

    /*
    | Seeded admin account (change the password immediately after first login).
    */
    'admin' => [
        'name' => env('SEED_ADMIN_NAME', 'Site Admin'),
        'email' => env('SEED_ADMIN_EMAIL', 'admin@example.com'),
        'password' => env('SEED_ADMIN_PASSWORD', 'password'),
    ],

    /*
    | Feature toggles. Frontends read these (via /api/v1/settings later) to decide
    | which sections to show. Keeps one codebase adaptable to many site shapes.
    */
    'features' => [
        'blog' => env('SITE_FEATURE_BLOG', true),
        'team' => env('SITE_FEATURE_TEAM', true),
        'contact' => env('SITE_FEATURE_CONTACT', true),
        'booking' => env('SITE_FEATURE_BOOKING', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Presets — per business type
    |--------------------------------------------------------------------------
    | `categories` seed the blog taxonomy; `sections` is a hint for frontends.
    | Add your own; keep `generic` as the neutral fallback.
    */
    'presets' => [
        'generic' => [
            'label' => 'Generic small business',
            'categories' => ['News', 'Updates'],
            'sections' => ['hero', 'about', 'blog', 'contact'],
        ],
        'media' => [
            'label' => 'Local media / author',
            'categories' => ['News', 'Stories', 'Interviews', 'Opinion'],
            'sections' => ['hero', 'featured', 'about', 'blog', 'contact'],
        ],
        'law' => [
            'label' => 'Law office / consultant',
            'categories' => ['Corporate', 'Civil', 'Criminal', 'Family', 'Labor'],
            'sections' => ['hero', 'services', 'team', 'blog', 'booking', 'contact'],
        ],
        'artist' => [
            'label' => 'Artist / craftsman',
            'categories' => ['Work', 'Exhibitions', 'Press'],
            'sections' => ['hero', 'gallery', 'about', 'blog', 'contact'],
        ],
        'shop' => [
            'label' => 'Small shop / service',
            'categories' => ['News', 'Offers'],
            'sections' => ['hero', 'services', 'about', 'contact'],
        ],
    ],

];
