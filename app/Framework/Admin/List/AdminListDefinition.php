<?php

namespace App\Framework\Admin\List;

class AdminListDefinition
{
    /**
     * @param  array<int, AdminListColumn>  $columns
     * @param  array<int, AdminListFilter>  $filters
     * @param  array<int, AdminListAction>  $rowActions
     * @param  array<int, AdminBulkAction>  $bulkActions
     * @param  array<int, string>  $searchFields
     * @param  array<int, int>  $perPageOptions
     * @param  \Closure(Builder, string): void|null  $searchQuery
     */
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $modelClass,
        public readonly array $columns,
        public readonly array $filters = [],
        public readonly array $rowActions = [],
        public readonly array $bulkActions = [],
        public readonly array $searchFields = [],
        public readonly ?\Closure $searchQuery = null,
        public readonly ?string $searchPlaceholder = null,
        public readonly string $defaultSort = 'id',
        public readonly string $defaultDirection = 'desc',
        public readonly int $defaultPerPage = 15,
        public readonly array $perPageOptions = [15, 25, 50, 100],
        public readonly bool $selectable = true,
    ) {}

    public function hasSearch(): bool
    {
        return $this->searchFields !== [] || $this->searchQuery !== null;
    }

    public function hasFilters(): bool
    {
        return $this->filters !== [];
    }

    public function filter(string $key): ?AdminListFilter
    {
        return collect($this->filters)->first(fn (AdminListFilter $filter) => $filter->key === $key);
    }

    public function bulkAction(string $key): ?AdminBulkAction
    {
        return collect($this->bulkActions)->first(fn (AdminBulkAction $action) => $action->key === $key);
    }

    public function sortableFields(): array
    {
        return collect($this->columns)
            ->filter(fn (AdminListColumn $column) => $column->sortable())
            ->mapWithKeys(fn (AdminListColumn $column) => [$column->key => $column->sortField])
            ->all();
    }

    public function visibleColumns(): array
    {
        return array_values(array_filter($this->columns, fn (AdminListColumn $column) => $column->visible));
    }
}
