<?php

namespace App\Framework\Admin\List;

use Illuminate\Database\Eloquent\Model;

class AdminListColumn
{
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly mixed $value = null,
        public readonly ?string $sortField = null,
        public readonly bool $visible = true,
        public readonly string $class = '',
        public readonly string $headerClass = '',
        public readonly bool $raw = false,
    ) {}

    public static function make(
        string $key,
        string $label,
        mixed $value = null,
        ?string $sortField = null,
        bool $visible = true,
        string $class = '',
        string $headerClass = '',
        bool $raw = false,
    ): self {
        return new self($key, $label, $value, $sortField, $visible, $class, $headerClass, $raw);
    }

    public function sortable(): bool
    {
        return $this->sortField !== null;
    }

    public function valueFor(Model $record, int $rowNumber): mixed
    {
        if ($this->key === 'no') {
            return $rowNumber;
        }

        if ($this->key === 'id') {
            return $record->getKey();
        }

        if (is_callable($this->value)) {
            return call_user_func($this->value, $record, $rowNumber);
        }

        if (is_string($this->value)) {
            return data_get($record, $this->value);
        }

        return data_get($record, $this->key);
    }
}
