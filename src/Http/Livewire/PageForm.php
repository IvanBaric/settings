<?php

declare(strict_types=1);

namespace IvanBaric\Settings\Http\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use IvanBaric\Settings\Events\SettingsPageSaved;
use IvanBaric\Settings\Repositories\SettingsRepository;
use IvanBaric\Settings\Support\SettingsFieldViewResolver;
use IvanBaric\Settings\Support\SettingsPage;
use IvanBaric\Settings\Support\SettingsRegistry;
use IvanBaric\Settings\Support\SettingsValidationFactory;
use Livewire\Component;

final class PageForm extends Component
{
    public string $pageName = '';

    /**
     * @var array<string, mixed>
     */
    public array $values = [];

    public function mount(string $pageName, SettingsRegistry $registry, SettingsRepository $repository): void
    {
        $this->pageName = $pageName;

        $page = $registry->page($pageName);

        abort_if($page === null, 404);

        $this->ensureAuthorized($page, true);

        $stored = $repository->allByPage($pageName);

        foreach ($page->fields() as $field) {
            $this->values[$field->name] = $stored[$field->name]?->value ?? $field->default;
        }
    }

    public function save(
        SettingsRegistry $registry,
        SettingsRepository $repository,
        SettingsValidationFactory $validationFactory,
    ): void {
        $page = $registry->page($this->pageName);

        abort_if($page === null, 404);

        if (! $this->ensureAuthorized($page)) {
            return;
        }

        $this->validate($validationFactory->rules($page));

        $payload = [];

        foreach ($page->fields() as $field) {
            $payload[$field->name] = array_key_exists($field->name, $this->values)
                ? $this->values[$field->name]
                : $field->default;
        }

        $repository->bulkSet($this->pageName, $payload);

        event(new SettingsPageSaved($this->pageName, $payload));

        $this->dispatch('toast', type: 'success', message: 'Postavke su spremljene.');
    }

    public function render(): View
    {
        $registry = app(SettingsRegistry::class);
        $page = $registry->page($this->pageName);

        abort_if($page === null, 404);

        return view('settings::livewire.page-form', [
            'page' => $page,
            'pages' => $registry->visiblePagesForUser(auth()->user()),
            'viewResolver' => app(SettingsFieldViewResolver::class),
        ])->layout((string) config('settings.ui.layout', 'layouts.app'));
    }

    protected function ensureAuthorized(SettingsPage $page, bool $abortOnFailure = false): bool
    {
        if ($page->permission === null) {
            return true;
        }

        $response = Gate::inspect($page->permission);

        if ($response->allowed()) {
            return true;
        }

        if ($abortOnFailure) {
            abort(403, $response->message() ?: 'This settings page is not available.');
        }

        $this->addError('authorization', $response->message() ?: 'You are not allowed to update this settings page.');
        $this->dispatch('toast', type: 'danger', message: $response->message() ?: 'You are not allowed to update this settings page.');

        return false;
    }
}
