<?php

declare(strict_types=1);

namespace IvanBaric\Settings\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use IvanBaric\Settings\Models\Setting;

final class SettingsRepository
{
    /**
     * @return Collection<string, Setting>
     */
    public function allByPage(string $page): Collection
    {
        /** @var Collection<string, Setting> $settings */
        $settings = $this->query()
            ->where('page', $page)
            ->get()
            ->keyBy('key');

        return $settings;
    }

    public function getValue(string $page, string $key, mixed $fallback = null): mixed
    {
        $setting = $this->query()
            ->where('page', $page)
            ->where('key', $key)
            ->first();

        return $setting?->value ?? $fallback;
    }

    public function set(string $page, string $key, mixed $value): Setting
    {
        $setting = $this->persist($page, $key, $value);

        $this->forgetCache($page, $key);

        return $setting;
    }

    /**
     * @param  array<string, mixed>  $values
     * @return Collection<string, Setting>
     */
    public function bulkSet(string $page, array $values): Collection
    {
        /** @var Collection<string, Setting> $saved */
        $saved = DB::transaction(function () use ($page, $values): Collection {
            $saved = collect();

            foreach ($values as $key => $value) {
                $saved->put((string) $key, $this->persist($page, (string) $key, $value));
            }

            return $saved;
        });

        foreach (array_keys($values) as $key) {
            $this->forgetCache($page, (string) $key);
        }

        return $saved;
    }

    protected function persist(string $page, string $key, mixed $value): Setting
    {
        /** @var Setting $setting */
        $setting = $this->query()->updateOrCreate(
            [
                'page' => $page,
                'key' => $key,
            ],
            [
                'value' => $value,
            ],
        );

        return $setting->refresh();
    }

    /**
     * @return Builder<Setting>
     */
    protected function query(): Builder
    {
        /** @var class-string<Setting> $modelClass */
        $modelClass = config('settings.models.setting', Setting::class);

        return (new $modelClass())->newQuery();
    }

    protected function forgetCache(string $page, string $key): void
    {
        if (! config('settings.cache.enabled', true)) {
            return;
        }

        cache()->forget($this->cacheKey($page, $key));
    }

    protected function cacheKey(string $page, string $key): string
    {
        return sprintf('%s.%s.%s', (string) config('settings.cache.prefix', 'settings'), $page, $key);
    }
}
