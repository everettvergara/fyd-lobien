<?php

namespace App\Modules\Menus\Services;

use App\Framework\Admin\List\AdminBulkAction;
use App\Framework\Admin\List\AdminListAction;
use App\Framework\Admin\List\AdminListColumn;
use App\Framework\Admin\List\AdminListDefinition;
use App\Framework\Admin\List\AdminListResult;
use App\Framework\Admin\List\AdminListService;
use App\Modules\Menus\Models\Menu;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class MenuAdminListService
{
    public function __construct(
        protected AdminListService $lists,
    ) {}

    public function result(Request $request): AdminListResult
    {
        $query = Menu::query()->withCount('allItems');

        return $this->lists->build($query, $this->definition(), $request);
    }

    public function definition(): AdminListDefinition
    {
        return new AdminListDefinition(
            id: 'menus',
            title: 'Menus',
            modelClass: Menu::class,
            columns: $this->columns(),
            rowActions: $this->rowActions(),
            bulkActions: $this->bulkActions(),
            searchFields: ['name'],
            searchPlaceholder: 'Search menu name...',
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
            AdminListColumn::make('name', 'Name', fn (Menu $menu) => sprintf(
                '<a href="%s" class="text-decoration-none fw-medium">%s</a>',
                route('admin.menus.edit', $menu),
                e($menu->name),
            ), sortField: 'name', raw: true),
            AdminListColumn::make('location', 'Location', fn (Menu $menu) => view('components.admin.status-badge', ['status' => $menu->location, 'variant' => 'secondary'])->render(), sortField: 'location', raw: true),
            AdminListColumn::make('items', 'Items', fn (Menu $menu) => (string) $menu->all_items_count, class: 'small'),
            AdminListColumn::make('updated_at', 'Updated', fn (Menu $menu) => $menu->updated_at->diffForHumans(), sortField: 'updated_at', class: 'text-muted small'),
        ];
    }

    protected function rowActions(): array
    {
        return [
            AdminListAction::make('edit', 'Edit', 'bi-pencil', fn (Menu $menu) => route('admin.menus.edit', $menu), ability: 'update'),
            AdminListAction::make(
                'delete',
                'Delete',
                'bi-trash',
                fn (Menu $menu) => route('admin.menus.destroy', $menu),
                method: 'DELETE',
                ability: 'delete',
                confirm: 'Delete this menu?',
                danger: true,
            ),
        ];
    }

    protected function bulkActions(): array
    {
        return [
            AdminBulkAction::make('delete', 'Delete selected', 'delete', fn (Collection $menus, Request $request) => $this->bulkDelete($menus), 'Delete selected menus?', danger: true),
        ];
    }

    protected function bulkDelete(Collection $menus): int
    {
        $menus->each(function (Menu $menu) {
            ActivityLogger::log('menus', 'deleted', $menu);
            $menu->delete();
        });

        return $menus->count();
    }
}
