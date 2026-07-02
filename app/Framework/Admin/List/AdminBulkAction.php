<?php

namespace App\Framework\Admin\List;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AdminBulkAction
{
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly ?string $ability,
        public readonly \Closure $handler,
        public readonly ?string $confirm = null,
        public readonly bool $danger = false,
        public readonly ?string $inputName = null,
        public readonly ?string $inputLabel = null,
        public readonly array|\Closure $inputOptions = [],
    ) {}

    public static function make(
        string $key,
        string $label,
        ?string $ability,
        \Closure $handler,
        ?string $confirm = null,
        bool $danger = false,
        ?string $inputName = null,
        ?string $inputLabel = null,
        array|\Closure $inputOptions = [],
    ): self {
        return new self($key, $label, $ability, $handler, $confirm, $danger, $inputName, $inputLabel, $inputOptions);
    }

    public function handle(Collection $records, Request $request): int
    {
        return (int) call_user_func($this->handler, $records, $request);
    }

    public function inputOptions(): array
    {
        if ($this->inputOptions instanceof \Closure) {
            return call_user_func($this->inputOptions);
        }

        return $this->inputOptions;
    }

    public function hasInput(): bool
    {
        return $this->inputName !== null;
    }
}
