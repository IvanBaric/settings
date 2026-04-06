<?php

declare(strict_types=1);

namespace IvanBaric\Settings\Support;

final class SettingsValidationFactory
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(SettingsPage $page): array
    {
        $rules = [];

        foreach ($page->fields() as $field) {
            $rules['values.'.$field->name] = $field->rules;
        }

        return $rules;
    }
}
