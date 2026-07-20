<?php

/*
| Allow client frontends (on other domains) to call the API.
| Set CORS_ALLOWED_ORIGINS in .env to a comma-separated list of your
| frontend origins in production, e.g. "https://klijent.rs,https://www.klijent.rs".
*/

return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => array_filter(
        explode(',', env('CORS_ALLOWED_ORIGINS', '*'))
    ),
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
