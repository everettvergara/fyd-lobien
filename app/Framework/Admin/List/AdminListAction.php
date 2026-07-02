<?php

namespace App\Framework\Admin\List;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

class AdminListAction
{
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly string $icon,
        public readonly \Closure $url,
        public readonly string $method = 'GET',
        public readonly ?string $ability = null,
        public readonly mixed $abilityTarget = null,
        public readonly ?\Closure $visible = null,
        public readonly ?string $confirm = null,
        public readonly bool $danger = false,
        public readonly bool $newTab = false,
    ) {}

    public static function make(
        string $key,
        string $label,
        string $icon,
        \Closure $url,
        string $method = 'GET',
        ?string $ability = null,
        mixed $abilityTarget = null,
        ?\Closure $visible = null,
        ?string $confirm = null,
        bool $danger = false,
        bool $newTab = false,
    ): self {
        return new self($key, $label, $icon, $url, $method, $ability, $abilityTarget, $visible, $confirm, $danger, $newTab);
    }

    public function urlFor(Model $record): string
    {
        return call_user_func($this->url, $record);
    }

    public function method(): string
    {
        return strtoupper($this->method);
    }

    public function visibleFor(Model $record): bool
    {
        if ($this->ability && ! Gate::allows($this->ability, $this->targetFor($record))) {
            return false;
        }

        if ($this->visible && ! call_user_func($this->visible, $record)) {
            return false;
        }

        return true;
    }

    protected function targetFor(Model $record): mixed
    {
        if ($this->abilityTarget instanceof \Closure) {
            return call_user_func($this->abilityTarget, $record);
        }

        return $this->abilityTarget ?? $record;
    }
}
