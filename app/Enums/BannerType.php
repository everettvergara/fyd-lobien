<?php

namespace App\Enums;

enum BannerType: string
{
    case Hero = 'hero';
    case Carousel = 'carousel';
    case Landing = 'landing';
    case Promotional = 'promotional';

    public function label(): string
    {
        return match ($this) {
            self::Hero => 'Hero Banner',
            self::Carousel => 'Carousel',
            self::Landing => 'Landing Banner',
            self::Promotional => 'Promotional Banner',
        };
    }
}
