<?php

declare(strict_types=1);

namespace IvanBaric\Settings\Support;

use IvanBaric\Corexis\Exceptions\InvalidConfiguration;
use IvanBaric\Corexis\Support\ConfigResolver;
use IvanBaric\Settings\Contracts\RegistersSettings;
use IvanBaric\Settings\Models\Setting;

final class SettingsConfigResolver
{
    /** @return class-string<Setting> */
    public static function settingModel(): string
    {
        return app(ConfigResolver::class)->model(
            key: 'settings.models.setting',
            default: Setting::class,
            expectedType: Setting::class,
        );
    }

    public static function settingsTable(): string
    {
        return app(ConfigResolver::class)->table(
            key: 'settings.table',
            default: 'settings',
        );
    }

    /**
     * @return list<class-string<RegistersSettings>>
     */
    public static function registrars(): array
    {
        $configured = config('settings.registrars', []);

        if (! is_array($configured)) {
            throw InvalidConfiguration::invalidClass(
                key: 'settings.registrars',
                value: $configured,
                expectedType: RegistersSettings::class,
            );
        }

        $registrars = [];

        foreach ($configured as $index => $registrar) {
            if ($registrar === null || $registrar === '') {
                continue;
            }

            if (! is_string($registrar)) {
                throw InvalidConfiguration::invalidClass(
                    key: 'settings.registrars.'.$index,
                    value: $registrar,
                    expectedType: RegistersSettings::class,
                );
            }

            $registrars[] = app(ConfigResolver::class)->implementation(
                key: 'settings.registrars.'.$index,
                default: $registrar,
                expectedType: RegistersSettings::class,
            );
        }

        return $registrars;
    }
}
