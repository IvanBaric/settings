<?php

declare(strict_types=1);

namespace IvanBaric\Settings\Enums;

enum FieldType: string
{
    case Text = 'text';
    case Textarea = 'textarea';
    case Boolean = 'boolean';
    case Select = 'select';
}
