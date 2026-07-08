<?php

namespace App\Modules\WebForms\Services;

use App\Framework\Admin\List\AdminListAction;
use App\Framework\Admin\List\AdminListColumn;
use App\Framework\Admin\List\AdminListDefinition;
use App\Framework\Admin\List\AdminListResult;
use App\Framework\Admin\List\AdminListService;
use App\Modules\WebForms\Models\Webform;
use Illuminate\Http\Request;

class WebformAdminListService
{
    public function __construct(
        protected AdminListService $lists,
    ) {}

    public function result(Request $request): AdminListResult
    {
        $query = Webform::query()->withCount('submissions');

        return $this->lists->build($query, $this->definition(), $request);
    }

    public function definition(): AdminListDefinition
    {
        return new AdminListDefinition(
            id: 'webforms',
            title: 'Webforms',
            modelClass: Webform::class,
            columns: [
                AdminListColumn::make('no', 'No', class: 'text-muted small', headerClass: 'text-muted'),
                AdminListColumn::make('id', 'ID', sortField: 'id', class: 'text-muted small', headerClass: 'text-muted'),
                AdminListColumn::make('name', 'Name', fn (Webform $webform) => sprintf(
                    '<a href="%s" class="text-decoration-none fw-medium">%s</a>',
                    route('admin.webforms.edit', $webform),
                    e($webform->name),
                ), sortField: 'name', raw: true),
                AdminListColumn::make('slug', 'Slug', fn (Webform $webform) => e($webform->slug), sortField: 'slug', class: 'small text-muted'),
                AdminListColumn::make('is_active', 'Active', fn (Webform $webform) => view('components.admin.status-badge', [
                    'status' => $webform->is_active ? 'Active' : 'Inactive',
                    'variant' => $webform->is_active ? 'success' : 'secondary',
                ])->render(), sortField: 'is_active', raw: true),
                AdminListColumn::make('submissions_count', 'Submissions', fn (Webform $webform) => (string) $webform->submissions_count, sortField: 'submissions_count', class: 'small'),
                AdminListColumn::make('updated_at', 'Updated', fn (Webform $webform) => $webform->updated_at->diffForHumans(), sortField: 'updated_at', class: 'text-muted small'),
            ],
            rowActions: [
                AdminListAction::make('edit', 'Edit', 'bi-pencil', fn (Webform $webform) => route('admin.webforms.edit', $webform), ability: 'update'),
                AdminListAction::make('builder', 'Builder', 'bi-sliders', fn (Webform $webform) => route('admin.webforms.builder', $webform), ability: 'update'),
                AdminListAction::make('submissions', 'Submissions', 'bi-inbox', fn (Webform $webform) => route('admin.webform-submissions.index', ['webform' => $webform->id]), ability: 'view'),
                AdminListAction::make('delete', 'Delete', 'bi-trash', fn (Webform $webform) => route('admin.webforms.destroy', $webform), method: 'DELETE', ability: 'delete', confirm: 'Delete this webform and all submissions?', danger: true),
            ],
            searchFields: ['name', 'slug'],
            searchPlaceholder: 'Search name or slug...',
            defaultSort: 'name',
            defaultDirection: 'asc',
            defaultPerPage: 15,
            selectable: false,
        );
    }
}
