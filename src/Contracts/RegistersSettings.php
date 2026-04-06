<?php

declare(strict_types=1);

namespace IvanBaric\Settings\Contracts;

use IvanBaric\Settings\Support\SettingsRegistry;

interface RegistersSettings
{
    public function register(SettingsRegistry $registry): void;
}
