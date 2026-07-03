<?php

namespace App\Framework;

class MenuItem
{
    public const PANEL_CORE = 'core';

    public const PANEL_BUSINESS = 'business';

    public function __construct(
        public readonly string $label,
        public readonly string $routeName,
        public readonly string $permission,
        public readonly string $icon,
        public readonly ?string $section = null,
        public readonly ?string $routePattern = null,
        public readonly int $sort = 0,
        public readonly array $query = [],
        public readonly string $panel = self::PANEL_CORE,
    ) {}

    public function isActive(): bool
    {
        if (! request()->routeIs($this->routePattern())) {
            return false;
        }

        if ($this->query === []) {
            if (request()->routeIs('admin.content.index')) {
                $contentType = request()->query('content_type');

                return $contentType === null || $contentType === '';
            }

            return true;
        }

        foreach ($this->query as $key => $value) {
            if ((string) request()->query($key) !== (string) $value) {
                return false;
            }
        }

        return true;
    }

    public function routePattern(): string
    {
        return $this->routePattern ?? $this->routeName.'.*';
    }
}
