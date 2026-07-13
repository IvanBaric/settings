<?php

declare(strict_types=1);

use IvanBaric\Settings\Models\Setting;

return [
    'table' => env('SETTINGS_TABLE', 'settings'),

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

    'permissions' => [
        [
            'name' => 'settings',
            'slug' => 'settings',
            'label' => 'settings::permissions.group',
            'description' => 'settings::permissions.description',
            'icon' => 'settings',
            'sort_order' => 50,
            'items' => [
                ['name' => 'View', 'slug' => 'view', 'code' => 'settings.view', 'label' => 'settings::permissions.view', 'sort_order' => 10],
                ['name' => 'Update', 'slug' => 'update', 'code' => 'settings.update', 'label' => 'settings::permissions.update', 'sort_order' => 20],
            ],
        ],
    ],
];
