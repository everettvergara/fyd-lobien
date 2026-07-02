<?php

namespace App\Modules\Roles\Services;

use App\Framework\Admin\List\AdminListAction;
use App\Framework\Admin\List\AdminListColumn;
use App\Framework\Admin\List\AdminListDefinition;
use App\Framework\Admin\List\AdminListResult;
use App\Framework\Admin\List\AdminListService;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleAdminListService
{
    public function __construct(
        protected AdminListService $lists,
    ) {}

    public function result(Request $request): AdminListResult
    {
        $query = Role::query()->withCount(['users', 'permissions']);

        return $this->lists->build($query, $this->definition(), $request);
    }

    public function definition(): AdminListDefinition
    {
        return new AdminListDefinition(
            id: 'roles',
            title: 'Roles',
            modelClass: Role::class,
            columns: $this->columns(),
            rowActions: $this->rowActions(),
            searchFields: ['name', 'display_name'],
            searchPlaceholder: 'Search role name...',
            defaultSort: 'display_name',
            defaultDirection: 'asc',
            defaultPerPage: 15,
        );
    }

    protected function columns(): array
    {
        return [
            AdminListColumn::make('no', 'No', class: 'text-muted small', headerClass: 'text-muted'),
            AdminListColumn::make('id', 'ID', sortField: 'id', class: 'text-muted small', headerClass: 'text-muted'),
            AdminListColumn::make('display_name', 'Role', fn (Role $role) => sprintf(
                '<a href="%s" class="text-decoration-none fw-medium">%s</a>%s',
                route('admin.roles.show', $role),
                e($role->display_name),
                $role->is_system ? ' <span class="badge bg-info-subtle text-info ms-1">System</span>' : '',
            ), sortField: 'display_name', raw: true),
            AdminListColumn::make('name', 'Key', 'name', sortField: 'name', class: 'text-muted small'),
            AdminListColumn::make('description', 'Description', fn (Role $role) => e(\Illuminate\Support\Str::limit($role->description ?? '', 60)), class: 'text-muted small'),
            AdminListColumn::make('users', 'Users', fn (Role $role) => (string) $role->users_count, class: 'small'),
            AdminListColumn::make('permissions', 'Permissions', fn (Role $role) => (string) $role->permissions_count, class: 'small'),
        ];
    }

    protected function rowActions(): array
    {
        return [
            AdminListAction::make('view', 'View', 'bi-eye', fn (Role $role) => route('admin.roles.show', $role), ability: 'view'),
            AdminListAction::make('edit', 'Edit', 'bi-pencil', fn (Role $role) => route('admin.roles.edit', $role), ability: 'update'),
            AdminListAction::make(
                'delete',
                'Delete',
                'bi-trash',
                fn (Role $role) => route('admin.roles.destroy', $role),
                method: 'DELETE',
                ability: 'delete',
                confirm: 'Are you sure?',
                danger: true,
            ),
        ];
    }
}
