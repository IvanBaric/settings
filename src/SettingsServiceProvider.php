<?php

declare(strict_types=1);

namespace IvanBaric\Settings;

use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use IvanBaric\Settings\Contracts\RegistersSettings;
use IvanBaric\Settings\Http\Livewire\PageForm;
use IvanBaric\Settings\Http\Livewire\SettingsIndex;
use IvanBaric\Settings\Repositories\SettingsRepository;
use IvanBaric\Settings\Support\SettingsFieldViewResolver;
use IvanBaric\Settings\Support\SettingsManager;
use IvanBaric\Settings\Support\SettingsRegistry;
use IvanBaric\Settings\Support\SettingsValidationFactory;
use Livewire\Livewire;

final class SettingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/settings.php', 'settings');

        $this->app->singleton(SettingsRegistry::class);
        $this->app->singleton(SettingsRepository::class);
        $this->app->singleton(SettingsManager::class);
        $this->app->singleton(SettingsValidationFactory::class);
        $this->app->singleton(SettingsFieldViewResolver::class);

        $this->app->alias(SettingsManager::class, 'settings');

        $helpers = __DIR__.'/helpers.php';

        if (file_exists($helpers)) {
            require_once $helpers;
        }
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->registerConfiguredPages();

        if ($this->shouldBootUi()) {
            $this->loadViewsFrom(__DIR__.'/../resources/views', 'settings');
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
            $this->registerLivewire();
        }

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/settings.php' => config_path('settings.php'),
        ], 'settings-config');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'settings-migrations');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/settings'),
        ], 'settings-views');
    }

    protected function registerConfiguredPages(): void
    {
        $registry = $this->app->make(SettingsRegistry::class);

        foreach ((array) config('settings.registrars', []) as $registrarClass) {
            if (! is_string($registrarClass) || $registrarClass === '') {
                continue;
            }

            $registrar = $this->app->make($registrarClass);

            if (! $registrar instanceof RegistersSettings) {
                throw new InvalidArgumentException("Configured settings registrar [{$registrarClass}] must implement ".RegistersSettings::class.'.');
            }

            $registry->registerRegistrar($registrar);
        }
    }

    protected function shouldBootUi(): bool
    {
        return (bool) config('settings.ui.enabled', true) && class_exists(Livewire::class);
    }

    protected function registerLivewire(): void
    {
        if (! class_exists(Livewire::class)) {
            return;
        }

        Livewire::component('settings.index', SettingsIndex::class);
        Livewire::component('settings.page-form', PageForm::class);
    }
}
