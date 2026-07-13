<?php

declare(strict_types=1);

namespace IvanBaric\Settings\Http\Livewire;

use Illuminate\Contracts\View\View;
use IvanBaric\Settings\Actions\SaveSettingsPageAction;
use IvanBaric\Settings\Models\Setting;
use IvanBaric\Settings\Repositories\SettingsRepository;
use IvanBaric\Settings\Support\SettingsFieldViewResolver;
use IvanBaric\Settings\Support\SettingsPage;
use IvanBaric\Settings\Support\SettingsRegistry;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class PageForm extends Component
{
    #[Locked]
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
            $setting = $stored->get($field->name);
            $this->values[$field->name] = $setting instanceof Setting
                ? $setting->value
                : $field->default;
        }
    }

    public function save(SaveSettingsPageAction $action): void
    {
        $result = $action->handle($this->pageName, $this->values);

        if ($result->failed()) {
            foreach ($result->errors as $field => $messages) {
                foreach ((array) $messages as $message) {
                    $this->addError((string) $field, (string) $message);
                }
            }

            if ($result->errors === []) {
                $this->addError('settings', $result->message);
            }

            $this->dispatch('toast', type: 'danger', message: $result->message);

            return;
        }

        $this->dispatch('toast', type: 'success', message: $result->message);
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
        $result = corexis_authorization_result($page->permission ?? 'settings.update');

        if ($result === null) {
            return true;
        }

        if ($abortOnFailure) {
            abort(403, $result->message ?: __('Ova stranica postavki nije dostupna.'));
        }

        $message = $result->message ?: __('Nemate ovlasti za uređivanje ove stranice postavki.');

        $this->addError('authorization', $message);
        $this->dispatch('toast', type: 'danger', message: $message);

        return false;
    }
}
