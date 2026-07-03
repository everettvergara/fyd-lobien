<?php

namespace App\Modules\PageManager\Services;

use App\Enums\ContentStatus;
use App\Framework\Admin\List\AdminListAction;
use App\Framework\Admin\List\AdminListColumn;
use App\Framework\Admin\List\AdminListDefinition;
use App\Framework\Admin\List\AdminListResult;
use App\Framework\Admin\List\AdminListService;
use App\Modules\PageManager\Models\Page;
use Illuminate\Http\Request;

class PageAdminListService
{
    public function __construct(
        protected AdminListService $lists,
    ) {}

    public function result(Request $request): AdminListResult
    {
        $query = Page::query()->with('seoMeta');

        return $this->lists->build($query, $this->definition(), $request);
    }

    public function definition(): AdminListDefinition
    {
        return new AdminListDefinition(
            id: 'pages',
            title: 'Pages',
            modelClass: Page::class,
            columns: $this->columns(),
            rowActions: $this->rowActions(),
            searchFields: ['title', 'path', 'slug'],
            searchPlaceholder: 'Search title or path...',
            defaultSort: 'updated_at',
            defaultDirection: 'desc',
            defaultPerPage: 15,
        );
    }

    protected function columns(): array
    {
        return [
            AdminListColumn::make('no', 'No', class: 'text-muted small', headerClass: 'text-muted'),
            AdminListColumn::make('id', 'ID', sortField: 'id', class: 'text-muted small', headerClass: 'text-muted'),
            AdminListColumn::make('path', 'Path', fn (Page $page) => e($page->path), sortField: 'path', class: 'font-monospace small'),
            AdminListColumn::make('title', 'Title', fn (Page $page) => sprintf(
                '<a href="%s" class="text-decoration-none fw-medium">%s</a>',
                route('admin.pages.edit', $page),
                e($page->title),
            ), sortField: 'title', raw: true),
            AdminListColumn::make('status', 'Status', fn (Page $page) => view('components.admin.status-badge', ['status' => $page->status])->render(), sortField: 'status', raw: true),
            AdminListColumn::make('sitemap', 'Sitemap', fn (Page $page) => ($page->seoMeta?->sitemap_include ?? true)
                ? '<span class="badge text-bg-success">Included</span>'
                : '<span class="badge text-bg-secondary">Excluded</span>', class: 'small', raw: true),
            AdminListColumn::make('updated_at', 'Updated', fn (Page $page) => $page->updated_at->diffForHumans(), sortField: 'updated_at', class: 'text-muted small'),
        ];
    }

    protected function rowActions(): array
    {
        return [
            AdminListAction::make('edit', 'Edit', 'bi-pencil', fn (Page $page) => route('admin.pages.edit', $page), ability: 'update'),
            AdminListAction::make('delete', 'Delete', 'bi-trash', fn (Page $page) => route('admin.pages.destroy', $page), method: 'DELETE', ability: 'delete', visible: fn (Page $page) => ! $page->is_system, confirm: 'Delete this page?', danger: true),
        ];
    }
}
