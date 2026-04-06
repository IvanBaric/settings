<?php

declare(strict_types=1);

use IvanBaric\Settings\Support\SettingsManager;

if (! function_exists('settings')) {
    function settings(): SettingsManager
    {
        return app(SettingsManager::class);
    }
}
