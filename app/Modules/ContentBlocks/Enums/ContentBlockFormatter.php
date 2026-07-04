<?php

namespace App\Modules\ContentBlocks\Enums;

enum ContentBlockFormatter: string
{
    case Unformatted = 'unformatted';
    case Table = 'table';
    case Ol = 'ol';
    case Ul = 'ul';

    public function label(): string
    {
        return match ($this) {
            self::Unformatted => 'Unformatted',
            self::Table => 'Table',
            self::Ol => 'Ordered List',
            self::Ul => 'Unordered List',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
