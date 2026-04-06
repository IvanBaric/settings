<?php

declare(strict_types=1);

namespace IvanBaric\Settings\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class SettingsPageSaved
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $values
     */
    public function __construct(
        public string $page,
        public array $values,
    ) {
    }
}
