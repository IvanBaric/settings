<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use IvanBaric\Settings\Http\Livewire\PageForm;
use IvanBaric\Settings\Http\Livewire\SettingsIndex;

Route::middleware((array) config('settings.ui.middleware', ['web', 'auth', 'verified']))
    ->prefix(trim((string) config('settings.ui.route_prefix', 'app/settings'), '/'))
    ->as('settings.pages.')
    ->group(function (): void {
        Route::get('/', SettingsIndex::class)->name('index');
        Route::get('/{pageName}', PageForm::class)->name('edit');
    });
