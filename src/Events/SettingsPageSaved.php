<?php

declare(strict_types=1);

namespace IvanBaric\Settings\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use IvanBaric\Corexis\Contracts\Events\DomainEvent;

final class SettingsPageSaved implements DomainEvent, ShouldDispatchAfterCommit
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $values
     */
    public function __construct(
        public string $page,
        public array $values,
    ) {}
}
