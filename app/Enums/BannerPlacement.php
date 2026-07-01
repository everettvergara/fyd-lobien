<?php

namespace App\Enums;

enum BannerPlacement: string
{
    case HomepageHero = 'homepage_hero';
    case HomepageSlider = 'homepage_slider';
    case Landing = 'landing';
    case Sidebar = 'sidebar';
    case Footer = 'footer';

    public function label(): string
    {
        return match ($this) {
            self::HomepageHero => 'Homepage Hero',
            self::HomepageSlider => 'Homepage Slider',
            self::Landing => 'Landing Pages',
            self::Sidebar => 'Sidebar',
            self::Footer => 'Footer',
        };
    }
}
