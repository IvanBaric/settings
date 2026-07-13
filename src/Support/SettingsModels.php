<?php

declare(strict_types=1);

namespace IvanBaric\Settings\Support;

use IvanBaric\Settings\Models\Setting;

final class SettingsModels
{
    /**
     * @return class-string<Setting>
     */
    public static function setting(): string
    {
        return SettingsConfigResolver::settingModel();
    }

    public static function table(): string
    {
        return SettingsConfigResolver::settingsTable();
    }
}
