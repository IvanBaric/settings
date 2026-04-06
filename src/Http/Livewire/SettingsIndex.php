<?php

declare(strict_types=1);

namespace IvanBaric\Settings\Http\Livewire;

use Illuminate\Contracts\View\View;
use IvanBaric\Settings\Support\SettingsRegistry;
use Livewire\Component;

final class SettingsIndex extends Component
{
    public function render(): View
    {
        return view('settings::livewire.settings-index', [
            'pages' => app(SettingsRegistry::class)->visiblePagesForUser(auth()->user()),
        ])->layout((string) config('settings.ui.layout', 'layouts.app'));
    }
}
