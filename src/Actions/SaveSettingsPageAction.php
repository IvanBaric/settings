<?php

declare(strict_types=1);

namespace IvanBaric\Settings\Actions;

use Illuminate\Support\Facades\Validator;
use IvanBaric\Corexis\Concerns\AuthorizesActions;
use IvanBaric\Corexis\Data\ActionResult;
use IvanBaric\Settings\Events\SettingsPageSaved;
use IvanBaric\Settings\Repositories\SettingsRepository;
use IvanBaric\Settings\Support\SettingsPage;
use IvanBaric\Settings\Support\SettingsRegistry;
use IvanBaric\Settings\Support\SettingsValidationFactory;

final readonly class SaveSettingsPageAction
{
    use AuthorizesActions;

    public function __construct(
        private SettingsRegistry $registry,
        private SettingsRepository $repository,
        private SettingsValidationFactory $validationFactory,
    ) {}

    /**
     * @param  array<string, mixed>  $values
     */
    public function handle(string $pageName, array $values): ActionResult
    {
        $page = $this->registry->page($pageName);

        if (! $page instanceof SettingsPage) {
            return ActionResult::error(
                message: __('Stranica postavki nije pronađena.'),
                code: 'settings_page_not_found',
            );
        }

        if ($result = $this->authorizeAction($page->permission ?? 'settings.update')) {
            return $result;
        }

        $validator = Validator::make(
            data: ['values' => $values],
            rules: $this->validationFactory->rules($page),
        );

        if ($validator->fails()) {
            return ActionResult::error(
                message: __('Provjerite unesene postavke i pokušajte ponovno.'),
                code: 'validation_failed',
                errors: $validator->errors()->toArray(),
            );
        }

        $payload = $this->payload($page, $values);

        $this->repository->bulkSet($pageName, $payload);

        event(new SettingsPageSaved($pageName, $payload));

        return ActionResult::success(
            message: __('Postavke su spremljene.'),
            data: $payload,
        );
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private function payload(SettingsPage $page, array $values): array
    {
        $payload = [];

        foreach ($page->fields() as $field) {
            $payload[$field->name] = array_key_exists($field->name, $values)
                ? $values[$field->name]
                : $field->default;
        }

        return $payload;
    }
}
