<?php

declare(strict_types=1);

namespace IvanBaric\Settings\Support;

use Illuminate\Support\Str;
use IvanBaric\Settings\Enums\FieldType;

final class PendingSettingField
{
    private ?string $label = null;

    private ?string $description = null;

    private mixed $default = null;

    /**
     * @var array<int, mixed>
     */
    private array $rules = [];

    /**
     * @var array<string, string>
     */
    private array $options = [];

    /**
     * @var array<string, mixed>
     */
    private array $meta = [];

    public function __construct(
        private readonly SettingsPage $page,
        private readonly string $name,
        private readonly FieldType $type,
    ) {
        $this->page->rememberField($this);
    }

    public function label(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function description(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function default(mixed $default): self
    {
        $this->default = $default;

        return $this;
    }

    /**
     * @param  array<int, mixed>  $rules
     */
    public function rules(array $rules): self
    {
        $this->rules = $rules;

        return $this;
    }

    /**
     * @param  array<string, string>  $options
     */
    public function options(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function meta(string $key, mixed $value): self
    {
        $this->meta[$key] = $value;

        return $this;
    }

    public function rows(int $rows): self
    {
        return $this->meta('rows', $rows);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function build(): SettingField
    {
        return new SettingField(
            name: $this->name,
            type: $this->type,
            label: $this->label ?? Str::headline($this->name),
            description: $this->description,
            default: $this->default,
            rules: $this->rules,
            options: $this->options,
            meta: $this->meta,
        );
    }
}
