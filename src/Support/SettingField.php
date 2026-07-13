<?php

declare(strict_types=1);

namespace IvanBaric\Settings\Support;

use IvanBaric\Settings\Enums\FieldType;

final readonly class SettingField
{
    /**
     * @param  array<int, mixed>  $rules
     * @param  array<string, string>  $options
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public string $name,
        public FieldType $type,
        public string $label,
        public ?string $description = null,
        public mixed $default = null,
        public array $rules = [],
        public array $options = [],
        public array $meta = [],
    ) {}

    public function meta(string $key, mixed $fallback = null): mixed
    {
        return $this->meta[$key] ?? $fallback;
    }

    public function isRequired(): bool
    {
        foreach ($this->rules as $rule) {
            if (! is_string($rule)) {
                continue;
            }

            if (in_array('required', explode('|', $rule), true)) {
                return true;
            }
        }

        return false;
    }
}
