<?php

namespace App\Framework;

class MenuItem
{
    public function __construct(
        public readonly string $label,
        public readonly string $routeName,
        public readonly string $permission,
        public readonly string $icon,
        public readonly ?string $section = null,
        public readonly ?string $routePattern = null,
        public readonly int $sort = 0,
    ) {}

    public function routePattern(): string
    {
        return $this->routePattern ?? $this->routeName.'.*';
    }
}
