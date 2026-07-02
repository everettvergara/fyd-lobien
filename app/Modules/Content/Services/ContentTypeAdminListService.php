<?php

namespace App\Modules\Content\Services;

use App\Framework\Admin\List\AdminListAction;
use App\Framework\Admin\List\AdminListColumn;
use App\Framework\Admin\List\AdminListDefinition;
use App\Framework\Admin\List\AdminListResult;
use App\Framework\Admin\List\AdminListService;
use App\Modules\Content\Models\Content;
use App\Modules\Content\Models\ContentType;
use App\Support\AdminIcon;
use Illuminate\Http\Request;

class ContentTypeAdminListService
{
    public function __construct(
        protected AdminListService $lists,
    ) {}

    public function result(Request $request): AdminListResult
    {
        $query = ContentType::query()->withCount('contents');

        return $this->lists->build($query, $this->definition(), $request);
    }

    public function definition(): AdminListDefinition
    {
        return new AdminListDefinition(
            id: 'content-types',
            title: 'Content Types',
            modelClass: ContentType::class,
            columns: $this->columns(),
            rowActions: $this->rowActions(),
            searchFields: ['key', 'label', 'description'],
            searchPlaceholder: 'Search key, label, or description...',
            defaultSort: 'sort_order',
            defaultDirection: 'asc',
            defaultPerPage: 15,
            selectable: false,
        );
    }

    protected function columns(): array
    {
        return [
            AdminListColumn::make('no', 'No', class: 'text-muted small', headerClass: 'text-muted'),
            AdminListColumn::make('id', 'ID', sortField: 'id', class: 'text-muted small', headerClass: 'text-muted'),
            AdminListColumn::make('label', 'Label', fn (ContentType $type) => sprintf(
                '<span class="fw-medium"><i class="%s me-1"></i>%s</span>',
                e(AdminIcon::solid($type->icon)),
                e($type->label),
            ), sortField: 'label', raw: true),
            AdminListColumn::make('key', 'Key', fn (ContentType $type) => '<code>'.e($type->key).'</code>', sortField: 'key', class: 'small', raw: true),
            AdminListColumn::make('description', 'Description', fn (ContentType $type) => e($type->description ?? '—'), class: 'text-muted small'),
            AdminListColumn::make('contents_count', 'Entries', fn (ContentType $type) => (string) $type->contents_count, sortField: 'contents_count', class: 'small'),
            AdminListColumn::make('is_active', 'Status', fn (ContentType $type) => view('components.admin.status-badge', [
                'status' => $type->is_active ? 'published' : 'archived',
                'label' => $type->is_active ? 'Active' : 'Inactive',
            ])->render(), sortField: 'is_active', raw: true, class: 'small'),
            AdminListColumn::make('sort_order', 'Sort', sortField: 'sort_order', class: 'text-muted small'),
        ];
    }

    protected function rowActions(): array
    {
        return [
            AdminListAction::make(
                'create-content',
                'Add Content',
                'bi-plus-lg',
                fn (ContentType $type) => route('admin.content.create', ['content_type' => $type->key]),
                ability: 'create',
                abilityTarget: fn () => Content::class,
                visible: fn (ContentType $type) => $type->is_active,
            ),
            AdminListAction::make('edit', 'Edit', 'bi-pencil', fn (ContentType $type) => route('admin.content-types.edit', $type), ability: 'update'),
            AdminListAction::make(
                'delete',
                'Delete',
                'bi-trash',
                fn (ContentType $type) => route('admin.content-types.destroy', $type),
                method: 'DELETE',
                ability: 'delete',
                confirm: 'Delete this content type?',
                danger: true,
                visible: fn (ContentType $type) => $type->contents_count === 0,
            ),
        ];
    }
}
