<?php

declare(strict_types=1);

namespace IvanBaric\Settings\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class SettingSaved
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public string $page,
        public string $key,
        public mixed $value,
    ) {
    }
}
