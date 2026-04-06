<?php

declare(strict_types=1);

namespace IvanBaric\Settings\Support;

use Illuminate\Support\Str;
use InvalidArgumentException;
use IvanBaric\Settings\Enums\FieldType;

final class SettingsPage
{
    /**
     * @var array<string, PendingSettingField>
     */
    private array $fields = [];

    private function __construct(
        public readonly string $name,
        public string $label,
        public ?string $group = null,
        public ?string $icon = null,
        public int $sortOrder = 0,
        public ?string $permission = null,
    ) {
    }

    public static function make(string $name): self
    {
        return new self(
            name: $name,
            label: Str::headline($name),
        );
    }

    public function label(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function group(?string $group): self
    {
        $this->group = $group;

        return $this;
    }

    public function icon(?string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function sortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    public function permission(?string $permission): self
    {
        $this->permission = $permission;

        return $this;
    }

    public function text(string $name): PendingSettingField
    {
        return new PendingSettingField($this, $name, FieldType::Text);
    }

    public function textarea(string $name): PendingSettingField
    {
        return new PendingSettingField($this, $name, FieldType::Textarea);
    }

    public function boolean(string $name): PendingSettingField
    {
        return new PendingSettingField($this, $name, FieldType::Boolean);
    }

    /**
     * @param  array<string, string>  $options
     */
    public function select(string $name, array $options = []): PendingSettingField
    {
        return (new PendingSettingField($this, $name, FieldType::Select))
            ->options($options);
    }

    public function rememberField(PendingSettingField $field): void
    {
        if (array_key_exists($field->name(), $this->fields)) {
            throw new InvalidArgumentException("Settings field [{$this->name}.{$field->name()}] is already registered.");
        }

        $this->fields[$field->name()] = $field;
    }

    /**
     * @return array<int, SettingField>
     */
    public function fields(): array
    {
        return array_map(
            static fn (PendingSettingField $field): SettingField => $field->build(),
            array_values($this->fields),
        );
    }

    public function field(string $name): ?SettingField
    {
        return $this->fields[$name]?->build() ?? null;
    }

    public function visibleTo(mixed $user): bool
    {
        if ($this->permission === null) {
            return true;
        }

        if ($user === null) {
            return false;
        }

        return (bool) $user->can($this->permission);
    }
}
