<?php

declare(strict_types=1);

namespace IvanBaric\Settings\Models;

use Illuminate\Database\Eloquent\Model;
use IvanBaric\Corexis\Concerns\HasUuid;
use IvanBaric\Settings\Support\SettingsConfigResolver;
use JsonException;

class Setting extends Model
{
    use HasUuid;

    protected $guarded = [
        'id',
        'uuid',
    ];

    public function getTable(): string
    {
        return SettingsConfigResolver::settingsTable();
    }

    public function getValueAttribute(?string $value): mixed
    {
        if ($value === null) {
            return null;
        }

        try {
            return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return $value;
        }
    }

    public function setValueAttribute(mixed $value): void
    {
        $this->attributes['value'] = json_encode($value, JSON_THROW_ON_ERROR);
    }
}
