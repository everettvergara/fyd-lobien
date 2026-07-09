<?php

namespace App\Modules\Newsletter\Services;

use App\Framework\Admin\List\AdminListAction;
use App\Framework\Admin\List\AdminListColumn;
use App\Framework\Admin\List\AdminListDefinition;
use App\Framework\Admin\List\AdminListResult;
use App\Framework\Admin\List\AdminListService;
use App\Modules\Newsletter\Models\NewsletterList;
use Illuminate\Http\Request;

class NewsletterListAdminListService
{
    public function __construct(
        protected AdminListService $lists,
    ) {}

    public function result(Request $request): AdminListResult
    {
        return $this->lists->build(NewsletterList::query(), $this->definition(), $request);
    }

    public function definition(): AdminListDefinition
    {
        return new AdminListDefinition(
            id: 'newsletter-lists',
            title: 'Newsletter Lists',
            modelClass: NewsletterList::class,
            columns: [
                AdminListColumn::make('no', 'No', class: 'text-muted small', headerClass: 'text-muted'),
                AdminListColumn::make('id', 'ID', sortField: 'id', class: 'text-muted small', headerClass: 'text-muted'),
                AdminListColumn::make('name', 'Name', sortField: 'name'),
                AdminListColumn::make('slug', 'Slug', fn (NewsletterList $list) => '<code>'.e($list->slug).'</code>', sortField: 'slug', raw: true, class: 'small'),
                AdminListColumn::make('is_active', 'Active', fn (NewsletterList $list) => $list->is_active
                    ? '<span class="badge text-bg-success">Yes</span>'
                    : '<span class="badge text-bg-secondary">No</span>', sortField: 'is_active', raw: true),
            ],
            rowActions: [
                AdminListAction::make('edit', 'Edit', 'bi-pencil', fn (NewsletterList $list) => route('admin.newsletter-lists.edit', $list), ability: 'update'),
                AdminListAction::make('delete', 'Delete', 'bi-trash', fn (NewsletterList $list) => route('admin.newsletter-lists.destroy', $list), method: 'DELETE', ability: 'delete', confirm: 'Delete this newsletter list and all subscribers?', danger: true),
            ],
            searchFields: ['name', 'slug'],
            defaultSort: 'name',
            defaultPerPage: 15,
        );
    }
}
