<?php

declare(strict_types=1);

namespace IvanBaric\Settings\Support;

use IvanBaric\Settings\Enums\FieldType;

final class SettingsFieldViewResolver
{
    public function resolve(FieldType $type): string
    {
        return match ($type) {
            FieldType::Text => 'settings::fields.text',
            FieldType::Textarea => 'settings::fields.textarea',
            FieldType::Boolean => 'settings::fields.boolean',
            FieldType::Select => 'settings::fields.select',
        };
    }
}
