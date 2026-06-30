<?php

use Illuminate\Support\Str;

$rawKeys = array_filter(array_map('trim', explode(',', (string) env('GOOGLE_PLACES_API_KEYS', env('GOOGLE_PLACES_API_KEY', '')))));

return [
    'database' => [
        'connection' => env('LOCATION_DATA_ENGINE_DB_CONNECTION'),
    ],

    'admin' => [
        'prefix' => env('LOCATION_DATA_ENGINE_ADMIN_PREFIX', 'admin/location-data-engine'),
        'middleware' => ['web', 'auth', 'location-data-engine.admin'],
        'page_size' => 24,
    ],

    'google' => [
        'api_keys' => $rawKeys,
        'base_url' => env('GOOGLE_PLACES_BASE_URL', 'https://maps.googleapis.com/maps/api/place'),
        'retry' => (int) env('GOOGLE_PLACES_RETRY', 3),
        'timeout' => (int) env('GOOGLE_PLACES_TIMEOUT', 20),
        'field_mask' => [
            'place_id',
            'name',
            'formatted_address',
            'geometry',
            'international_phone_number',
            'formatted_phone_number',
            'website',
            'rating',
            'user_ratings_total',
            'types',
            'business_status',
            'opening_hours',
            'utc_offset',
            'address_component',
            'url',
            'photos',
            'reviews',
        ],
    ],

    'scan' => [
        'default_radius' => (int) env('LOCATION_DATA_ENGINE_RADIUS', 25000),
        'default_limit' => (int) env('LOCATION_DATA_ENGINE_LIMIT', 250),
        'grid_precision' => (int) env('LOCATION_DATA_ENGINE_GRID_PRECISION', 5),
        'chunk_size' => (int) env('LOCATION_DATA_ENGINE_CHUNK_SIZE', 25),
        'queue' => env('LOCATION_DATA_ENGINE_QUEUE', 'default'),
        'cache_ttl' => (int) env('LOCATION_DATA_ENGINE_CACHE_TTL', 3600),
        'proxy_support' => (bool) env('LOCATION_DATA_ENGINE_PROXY_SUPPORT', false),
        'multi_api_keys' => true,
    ],

    'images' => [
        'enabled' => (bool) env('LOCATION_DATA_ENGINE_IMAGES_ENABLED', true),
        'disk' => env('LOCATION_DATA_ENGINE_IMAGE_DISK', 'public'),
        'directory' => env('LOCATION_DATA_ENGINE_IMAGE_DIRECTORY', 'location-data-engine/places'),
        'create_thumbnails' => true,
        'thumbnail_width' => 480,
    ],

    'enrichment' => [
        'enabled' => (bool) env('LOCATION_DATA_ENGINE_ENRICHMENT_ENABLED', true),
        'timeout' => (int) env('LOCATION_DATA_ENGINE_ENRICHMENT_TIMEOUT', 15),
        'contact_paths' => ['contact', 'contact-us', 'nous-joindre', 'booking', 'reservation'],
    ],

    'categories' => [
        'hotels' => ['google_type' => 'lodging', 'search_terms' => ['hotels', 'boutique hotels', 'resorts']],
        'restaurants' => ['google_type' => 'restaurant', 'search_terms' => ['restaurants', 'cafes', 'bistros']],
        'tourism' => ['google_type' => 'tourist_attraction', 'search_terms' => ['tourism', 'attractions', 'destinations']],
        'agencies' => ['google_type' => 'travel_agency', 'search_terms' => ['travel agencies', 'tour operators']],
        'attractions' => ['google_type' => 'tourist_attraction', 'search_terms' => ['attractions', 'experiences']],
        'shopping' => ['google_type' => 'shopping_mall', 'search_terms' => ['shopping', 'malls', 'stores']],
        'services' => ['google_type' => 'point_of_interest', 'search_terms' => ['services', 'companies', 'businesses']],
    ],

    'exports' => [
        'csv_delimiter' => ';',
        'excel_writer' => 'Xlsx',
    ],

    'ui' => [
        'brand' => 'Location Data Engine',
        'result_badge_palette' => ['primary', 'success', 'warning', 'info', 'dark'],
    ],
];
