<?php

declare(strict_types=1);

namespace IvanBaric\Settings;

use Illuminate\Support\ServiceProvider;
use IvanBaric\Settings\Http\Livewire\PageForm;
use IvanBaric\Settings\Http\Livewire\SettingsIndex;
use IvanBaric\Settings\Repositories\SettingsRepository;
use IvanBaric\Settings\Support\SettingsFieldViewResolver;
use IvanBaric\Settings\Support\SettingsConfigResolver;
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
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'settings');

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

        $this->publishes([
            __DIR__.'/../lang' => lang_path('vendor/settings'),
        ], 'settings-translations');
    }

    protected function registerConfiguredPages(): void
    {
        $registry = $this->app->make(SettingsRegistry::class);

        foreach (SettingsConfigResolver::registrars() as $registrarClass) {
            $registrar = $this->app->make($registrarClass);
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
