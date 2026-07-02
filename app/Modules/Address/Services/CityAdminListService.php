<?php

namespace App\Modules\Address\Services;

use App\Framework\Admin\List\AdminListAction;
use App\Framework\Admin\List\AdminListColumn;
use App\Framework\Admin\List\AdminListDefinition;
use App\Framework\Admin\List\AdminListFilter;
use App\Framework\Admin\List\AdminListResult;
use App\Framework\Admin\List\AdminListService;
use App\Modules\Address\Models\City;
use App\Modules\Address\Models\Province;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CityAdminListService
{
    public function __construct(
        protected AdminListService $lists,
    ) {}

    public function result(Request $request): AdminListResult
    {
        $query = City::query()->with('province');

        return $this->lists->build($query, $this->definition(), $request);
    }

    public function definition(): AdminListDefinition
    {
        return new AdminListDefinition(
            id: 'cities',
            title: 'Cities',
            modelClass: City::class,
            columns: $this->columns(),
            filters: $this->filters(),
            rowActions: $this->rowActions(),
            searchFields: ['name'],
            searchPlaceholder: 'Search city name...',
            defaultSort: 'name',
            defaultDirection: 'asc',
            defaultPerPage: 15,
        );
    }

    protected function columns(): array
    {
        return [
            AdminListColumn::make('no', 'No', class: 'text-muted small', headerClass: 'text-muted'),
            AdminListColumn::make('id', 'ID', sortField: 'id', class: 'text-muted small', headerClass: 'text-muted'),
            AdminListColumn::make('name', 'Name', fn (City $city) => sprintf(
                '<a href="%s" class="text-decoration-none fw-medium">%s</a>',
                route('admin.cities.show', $city),
                e($city->name),
            ), sortField: 'name', raw: true),
            AdminListColumn::make('province', 'Province', fn (City $city) => sprintf(
                '<a href="%s" class="text-decoration-none">%s</a>',
                route('admin.provinces.show', $city->province),
                e($city->province->name),
            ), sortField: 'province_id', raw: true),
            AdminListColumn::make('is_active', 'Active', fn (City $city) => $city->is_active
                ? '<span class="badge bg-success-subtle text-success">Active</span>'
                : '<span class="badge bg-secondary-subtle text-secondary">Inactive</span>', sortField: 'is_active', raw: true),
        ];
    }

    protected function filters(): array
    {
        return [
            AdminListFilter::make(
                'province_id',
                'Province',
                'select',
                fn () => Province::query()->orderBy('name')->pluck('name', 'id')->all(),
                fn (Builder $query, string $value) => $query->where('province_id', $value),
            ),
        ];
    }

    protected function rowActions(): array
    {
        return [
            AdminListAction::make('view', 'View', 'bi-eye', fn (City $city) => route('admin.cities.show', $city), ability: 'view'),
            AdminListAction::make('edit', 'Edit', 'bi-pencil', fn (City $city) => route('admin.cities.edit', $city), ability: 'update'),
            AdminListAction::make(
                'delete',
                'Delete',
                'bi-trash',
                fn (City $city) => route('admin.cities.destroy', $city),
                method: 'DELETE',
                ability: 'delete',
                confirm: 'Are you sure?',
                danger: true,
            ),
        ];
    }
}
