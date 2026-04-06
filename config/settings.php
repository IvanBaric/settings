<?php

declare(strict_types=1);

use IvanBaric\Settings\Models\Setting;

return [
    'models' => [
        'setting' => Setting::class,
    ],

    'registrars' => [
        // App\Settings\ContentSettingsRegistrar::class,
    ],

    'cache' => [
        'enabled' => env('SETTINGS_CACHE_ENABLED', true),
        'ttl' => (int) env('SETTINGS_CACHE_TTL', 3600),
        'prefix' => env('SETTINGS_CACHE_PREFIX', 'settings'),
    ],

    'ui' => [
        'enabled' => env('SETTINGS_UI_ENABLED', true),
        'layout' => 'layouts.app',
        'route_prefix' => env('SETTINGS_UI_ROUTE_PREFIX', 'app/settings'),
        'middleware' => ['web', 'auth', 'verified'],
    ],
];
