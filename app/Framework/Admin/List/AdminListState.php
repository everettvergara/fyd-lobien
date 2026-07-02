<?php

namespace App\Framework\Admin\List;

use Illuminate\Http\Request;

class AdminListState
{
    public function __construct(
        public readonly ?string $search,
        public readonly array $filters,
        public readonly string $sort,
        public readonly string $direction,
        public readonly int $perPage,
        public readonly array $selected = [],
    ) {}

    public static function fromRequest(Request $request, AdminListDefinition $definition): self
    {
        $sortable = $definition->sortableFields();
        $sort = $request->string('sort')->toString() ?: $definition->defaultSort;

        if (! array_key_exists($sort, $sortable)) {
            $sort = $definition->defaultSort;
        }

        $direction = strtolower($request->string('direction')->toString());
        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = $definition->defaultDirection;
        }

        $perPage = $request->integer('per_page', $definition->defaultPerPage);
        if (! in_array($perPage, $definition->perPageOptions, true)) {
            $perPage = $definition->defaultPerPage;
        }

        $filters = [];
        foreach ($definition->filters as $filter) {
            $filters[$filter->key] = $request->input($filter->key, $filter->default);
        }

        return new self(
            search: trim($request->string('search')->toString()) ?: null,
            filters: $filters,
            sort: $sort,
            direction: $direction,
            perPage: $perPage,
            selected: array_filter((array) $request->input('selected', [])),
        );
    }

    public function activeFilters(): array
    {
        return array_filter($this->filters, fn ($value) => $value !== null && $value !== '');
    }

    public function query(): array
    {
        return array_filter(array_merge([
            'search' => $this->search,
            'sort' => $this->sort,
            'direction' => $this->direction,
            'per_page' => $this->perPage,
        ], $this->activeFilters()), fn ($value) => $value !== null && $value !== '');
    }
}
