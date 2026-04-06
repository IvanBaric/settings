<?php

declare(strict_types=1);

namespace IvanBaric\Settings\Models;

use Illuminate\Database\Eloquent\Model;
use JsonException;

class Setting extends Model
{
    protected $table = 'settings';

    protected $guarded = [];

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
