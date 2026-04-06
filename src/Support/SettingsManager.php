<?php

declare(strict_types=1);

namespace IvanBaric\Settings\Support;

use InvalidArgumentException;
use IvanBaric\Settings\Enums\FieldType;
use IvanBaric\Settings\Events\SettingSaved;
use IvanBaric\Settings\Models\Setting;
use IvanBaric\Settings\Repositories\SettingsRepository;

final class SettingsManager
{
    public function __construct(
        private readonly SettingsRegistry $registry,
        private readonly SettingsRepository $repository,
    ) {
    }

    public function page(string $page): ?SettingsPage
    {
        return $this->registry->page($page);
    }

    public function get(string $page, string $key, mixed $fallback = null): mixed
    {
        $resolved = config('settings.cache.enabled', true)
            ? cache()->remember(
                $this->cacheKey($page, $key),
                (int) config('settings.cache.ttl', 3600),
                fn (): array => $this->resolveValue($page, $key),
            )
            : $this->resolveValue($page, $key);

        return $resolved['resolved'] ? $resolved['value'] : $fallback;
    }

    public function set(string $page, string $key, mixed $value): Setting
    {
        $field = $this->registeredField($page, $key);
        $setting = $this->repository->set($page, $key, $this->normalizeForStorage($field, $value));

        event(new SettingSaved($page, $key, $setting->value));

        return $setting;
    }

    public function string(string $page, string $key, string $fallback = ''): string
    {
        $value = $this->get($page, $key, $fallback);

        if (is_string($value)) {
            return $value;
        }

        return is_scalar($value) ? (string) $value : $fallback;
    }

    public function boolean(string $page, string $key, bool $fallback = false): bool
    {
        $value = $this->get($page, $key, $fallback);

        if (is_bool($value)) {
            return $value;
        }

        return filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? $fallback;
    }

    public function integer(string $page, string $key, int $fallback = 0): int
    {
        $value = $this->get($page, $key, $fallback);

        return is_numeric($value) ? (int) $value : $fallback;
    }

    public function float(string $page, string $key, float $fallback = 0.0): float
    {
        $value = $this->get($page, $key, $fallback);

        return is_numeric($value) ? (float) $value : $fallback;
    }

    /**
     * @param  array<int|string, mixed>  $fallback
     * @return array<int|string, mixed>
     */
    public function arrayValue(string $page, string $key, array $fallback = []): array
    {
        $value = $this->get($page, $key, $fallback);

        return is_array($value) ? $value : $fallback;
    }

    /**
     * @return array{resolved: bool, value: mixed}
     */
    protected function resolveValue(string $page, string $key): array
    {
        $pageDefinition = $this->registry->page($page);
        $field = $pageDefinition?->field($key);
        $missing = '__settings_missing__';

        $value = $this->repository->getValue($page, $key, $missing);

        if ($value === $missing) {
            if ($field instanceof SettingField) {
                return [
                    'resolved' => true,
                    'value' => $field->default,
                ];
            }

            return [
                'resolved' => false,
                'value' => null,
            ];
        }

        return [
            'resolved' => true,
            'value' => $field instanceof SettingField
                ? $this->normalizeResolvedValue($field, $value)
                : $value,
        ];
    }

    protected function registeredField(string $page, string $key): SettingField
    {
        $pageDefinition = $this->registry->page($page);

        if (! $pageDefinition instanceof SettingsPage) {
            throw new InvalidArgumentException("Settings page [{$page}] is not registered.");
        }

        $field = $pageDefinition->field($key);

        if (! $field instanceof SettingField) {
            throw new InvalidArgumentException("Settings field [{$page}.{$key}] is not registered.");
        }

        return $field;
    }

    protected function normalizeForStorage(SettingField $field, mixed $value): mixed
    {
        return match ($field->type) {
            FieldType::Boolean => (bool) $value,
            default => $value,
        };
    }

    protected function normalizeResolvedValue(SettingField $field, mixed $value): mixed
    {
        return match ($field->type) {
            FieldType::Boolean => filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? (bool) $value,
            FieldType::Text, FieldType::Textarea, FieldType::Select => $value === null ? null : (string) $value,
        };
    }

    protected function cacheKey(string $page, string $key): string
    {
        return sprintf('%s.%s.%s', (string) config('settings.cache.prefix', 'settings'), $page, $key);
    }
}
