<?php

namespace App\Framework\Admin\List;

use Illuminate\Database\Eloquent\Builder;

class AdminListFilter
{
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly string $type = 'text',
        public readonly array|\Closure $options = [],
        public readonly ?\Closure $query = null,
        public readonly mixed $default = null,
    ) {}

    public static function make(
        string $key,
        string $label,
        string $type = 'text',
        array|\Closure $options = [],
        ?\Closure $query = null,
        mixed $default = null,
    ): self {
        return new self($key, $label, $type, $options, $query, $default);
    }

    public function apply(Builder $query, mixed $value): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if ($this->query) {
            call_user_func($this->query, $query, $value);
        }
    }

    public function options(): array
    {
        if ($this->options instanceof \Closure) {
            return call_user_func($this->options);
        }

        return $this->options;
    }
}
