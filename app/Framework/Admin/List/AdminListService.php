<?php

namespace App\Framework\Admin\List;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AdminListService
{
    public function build(Builder $query, AdminListDefinition $definition, Request $request): AdminListResult
    {
        $state = AdminListState::fromRequest($request, $definition);

        $this->applySearch($query, $definition, $state);
        $this->applyFilters($query, $definition, $state);
        $this->applySorting($query, $definition, $state);

        $records = $query->paginate($state->perPage)->withQueryString();

        return new AdminListResult($definition, $state, $records);
    }

    protected function applySearch(Builder $query, AdminListDefinition $definition, AdminListState $state): void
    {
        if (! $state->search || ! $definition->hasSearch()) {
            return;
        }

        $search = $state->search;

        if ($definition->searchQuery) {
            call_user_func($definition->searchQuery, $query, $search);

            return;
        }

        $query->where(function (Builder $query) use ($definition, $search) {
            foreach ($definition->searchFields as $field) {
                $query->orWhere($field, 'like', "%{$search}%");
            }
        });
    }

    protected function applyFilters(Builder $query, AdminListDefinition $definition, AdminListState $state): void
    {
        foreach ($state->activeFilters() as $key => $value) {
            $definition->filter($key)?->apply($query, $value);
        }
    }

    protected function applySorting(Builder $query, AdminListDefinition $definition, AdminListState $state): void
    {
        $sortable = $definition->sortableFields();
        $field = $sortable[$state->sort] ?? $sortable[$definition->defaultSort] ?? 'id';

        $query->orderBy($field, $state->direction);
    }
}
