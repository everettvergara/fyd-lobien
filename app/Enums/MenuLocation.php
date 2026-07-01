<?php

namespace App\Enums;

enum MenuLocation: string
{
    case Header = 'header';
    case Footer = 'footer';
    case Sidebar = 'sidebar';

    public function label(): string
    {
        return match ($this) {
            self::Header => 'Header Menu',
            self::Footer => 'Footer Menu',
            self::Sidebar => 'Sidebar Menu',
        };
    }
}
