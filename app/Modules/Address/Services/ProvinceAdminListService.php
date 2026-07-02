<?php

namespace App\Modules\Address\Services;

use App\Framework\Admin\List\AdminListAction;
use App\Framework\Admin\List\AdminListColumn;
use App\Framework\Admin\List\AdminListDefinition;
use App\Framework\Admin\List\AdminListResult;
use App\Framework\Admin\List\AdminListService;
use App\Modules\Address\Models\Province;
use Illuminate\Http\Request;

class ProvinceAdminListService
{
    public function __construct(
        protected AdminListService $lists,
    ) {}

    public function result(Request $request): AdminListResult
    {
        $query = Province::query()->withCount('cities');

        return $this->lists->build($query, $this->definition(), $request);
    }

    public function definition(): AdminListDefinition
    {
        return new AdminListDefinition(
            id: 'provinces',
            title: 'Provinces',
            modelClass: Province::class,
            columns: $this->columns(),
            rowActions: $this->rowActions(),
            searchFields: ['name', 'code'],
            searchPlaceholder: 'Search province name or code...',
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
            AdminListColumn::make('name', 'Name', fn (Province $province) => sprintf(
                '<a href="%s" class="text-decoration-none fw-medium">%s</a>',
                route('admin.provinces.show', $province),
                e($province->name),
            ), sortField: 'name', raw: true),
            AdminListColumn::make('code', 'Code', 'code', sortField: 'code', class: 'text-muted small'),
            AdminListColumn::make('cities', 'Cities', fn (Province $province) => (string) $province->cities_count, class: 'small'),
            AdminListColumn::make('is_active', 'Active', fn (Province $province) => $province->is_active
                ? '<span class="badge bg-success-subtle text-success">Active</span>'
                : '<span class="badge bg-secondary-subtle text-secondary">Inactive</span>', sortField: 'is_active', raw: true),
        ];
    }

    protected function rowActions(): array
    {
        return [
            AdminListAction::make('view', 'View', 'bi-eye', fn (Province $province) => route('admin.provinces.show', $province), ability: 'view'),
            AdminListAction::make('edit', 'Edit', 'bi-pencil', fn (Province $province) => route('admin.provinces.edit', $province), ability: 'update'),
            AdminListAction::make(
                'delete',
                'Delete',
                'bi-trash',
                fn (Province $province) => route('admin.provinces.destroy', $province),
                method: 'DELETE',
                ability: 'delete',
                confirm: 'Are you sure?',
                danger: true,
            ),
        ];
    }
}
