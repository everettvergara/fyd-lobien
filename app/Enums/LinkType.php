<?php

namespace App\Enums;

enum LinkType: string
{
    case Internal = 'internal';
    case External = 'external';

    public function label(): string
    {
        return match ($this) {
            self::Internal => 'Internal Link',
            self::External => 'External Link',
        };
    }
}
