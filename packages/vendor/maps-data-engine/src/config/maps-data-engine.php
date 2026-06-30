<?php

return [
    'database' => [
        'connection' => env('MAPS_DATA_ENGINE_DB_CONNECTION'),
    ],

    'admin' => [
        'prefix' => env('MAPS_DATA_ENGINE_ADMIN_PREFIX', 'admin/maps-data-engine'),
        'middleware' => ['web', 'auth', 'maps-data-engine.admin'],
        'page_size' => 24,
    ],

    'runtime' => [
        'queue' => env('MAPS_DATA_ENGINE_QUEUE', 'default'),
        'node_binary' => env('MAPS_DATA_ENGINE_NODE_BINARY', 'node'),
        'working_directory' => base_path('packages/vendor/maps-data-engine/nodejs'),
        'runner_script' => base_path('packages/vendor/maps-data-engine/nodejs/scrapers/run-google-maps.js'),
        'payload_directory' => storage_path('app/maps-data-engine/payloads'),
        'result_directory' => storage_path('app/maps-data-engine/results'),
        'screenshots_directory' => storage_path('app/maps-data-engine/screenshots'),
        'sessions_directory' => storage_path('app/maps-data-engine/sessions'),
        'max_concurrency' => (int) env('MAPS_DATA_ENGINE_MAX_CONCURRENCY', 1),
        'slowdown_ms_min' => (int) env('MAPS_DATA_ENGINE_SLOWDOWN_MIN', 800),
        'slowdown_ms_max' => (int) env('MAPS_DATA_ENGINE_SLOWDOWN_MAX', 2500),
        'max_businesses_per_segment' => (int) env('MAPS_DATA_ENGINE_MAX_BUSINESSES_PER_SEGMENT', 80),
        'browser_pool_size' => (int) env('MAPS_DATA_ENGINE_BROWSER_POOL_SIZE', 2),
        'headless' => (bool) env('MAPS_DATA_ENGINE_HEADLESS', true),
        'dispatch_spacing_seconds' => (int) env('MAPS_DATA_ENGINE_DISPATCH_SPACING', 12),
    ],

    'proxy' => [
        'enabled' => (bool) env('MAPS_DATA_ENGINE_PROXY_ENABLED', false),
        'rotation_mode' => env('MAPS_DATA_ENGINE_PROXY_ROTATION', 'smart'),
        'blacklist_ttl' => (int) env('MAPS_DATA_ENGINE_PROXY_BLACKLIST_TTL', 1800),
        'retry_other_proxy_after_failures' => (int) env('MAPS_DATA_ENGINE_PROXY_RETRY_AFTER', 1),
    ],

    'scraping' => [
        'default_radius' => (int) env('MAPS_DATA_ENGINE_RADIUS', 18000),
        'default_limit' => (int) env('MAPS_DATA_ENGINE_LIMIT', 120),
        'segment_batch_size' => (int) env('MAPS_DATA_ENGINE_SEGMENT_BATCH_SIZE', 5),
        'categories' => [
            'hotels' => ['Hotels', 'Boutique hotels', 'Resorts'],
            'restaurants' => ['Restaurants', 'Bistros', 'Cafes'],
            'tourism' => ['Tourism', 'Tourist attractions', 'Experiences'],
            'agencies' => ['Travel agencies', 'Tour operators'],
            'shopping' => ['Shopping', 'Stores', 'Malls'],
            'services' => ['Services', 'Companies', 'Businesses'],
        ],
    ],

    'grid' => [
        'enabled' => (bool) env('MAPS_DATA_ENGINE_GRID_ENABLED', true),
        'step_km' => (float) env('MAPS_DATA_ENGINE_GRID_STEP_KM', 3.5),
        'points_per_anchor' => (int) env('MAPS_DATA_ENGINE_GRID_POINTS', 5),
    ],

    'security' => [
        'captcha_cooldown_minutes' => (int) env('MAPS_DATA_ENGINE_CAPTCHA_COOLDOWN', 30),
        'incident_screenshot' => true,
        'encrypt_proxy_credentials' => true,
    ],

    'exports' => [
        'csv_delimiter' => ';',
    ],
];
