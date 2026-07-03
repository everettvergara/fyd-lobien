<?php

namespace App\Modules\DemoNotes\Services;

use App\Framework\Admin\List\AdminListAction;
use App\Framework\Admin\List\AdminListColumn;
use App\Framework\Admin\List\AdminListDefinition;
use App\Framework\Admin\List\AdminListResult;
use App\Framework\Admin\List\AdminListService;
use App\Modules\DemoNotes\Models\DemoTag;
use Illuminate\Http\Request;

class DemoTagAdminListService
{
    public function __construct(
        protected AdminListService $lists,
    ) {}

    public function result(Request $request): AdminListResult
    {
        return $this->lists->build(DemoTag::query(), $this->definition(), $request);
    }

    public function definition(): AdminListDefinition
    {
        return new AdminListDefinition(
            id: 'demo-tags',
            title: 'Demo Tags',
            modelClass: DemoTag::class,
            columns: [
                AdminListColumn::make('no', 'No', class: 'text-muted small', headerClass: 'text-muted'),
                AdminListColumn::make('id', 'ID', sortField: 'id', class: 'text-muted small', headerClass: 'text-muted'),
                AdminListColumn::make('name', 'Name', fn (DemoTag $tag) => e($tag->name), sortField: 'name'),
            ],
            rowActions: [
                AdminListAction::make('edit', 'Edit', 'bi-pencil', fn (DemoTag $tag) => route('admin.demo-tags.edit', $tag), ability: 'update'),
                AdminListAction::make('delete', 'Delete', 'bi-trash', fn (DemoTag $tag) => route('admin.demo-tags.destroy', $tag), method: 'DELETE', ability: 'delete', confirm: 'Delete this tag?', danger: true),
            ],
            searchFields: ['name'],
            searchPlaceholder: 'Search tag name...',
            defaultSort: 'name',
            defaultDirection: 'asc',
            defaultPerPage: 15,
        );
    }
}
