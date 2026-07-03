<?php

namespace App\Modules\DemoNotes\Services;

use App\Framework\Admin\List\AdminListAction;
use App\Framework\Admin\List\AdminListColumn;
use App\Framework\Admin\List\AdminListDefinition;
use App\Framework\Admin\List\AdminListResult;
use App\Framework\Admin\List\AdminListService;
use App\Modules\DemoNotes\Models\DemoNote;
use Illuminate\Http\Request;

class DemoNoteAdminListService
{
    public function __construct(
        protected AdminListService $lists,
    ) {}

    public function result(Request $request): AdminListResult
    {
        return $this->lists->build(DemoNote::query(), $this->definition(), $request);
    }

    public function definition(): AdminListDefinition
    {
        return new AdminListDefinition(
            id: 'demo-notes',
            title: 'Demo Notes',
            modelClass: DemoNote::class,
            columns: [
                AdminListColumn::make('no', 'No', class: 'text-muted small', headerClass: 'text-muted'),
                AdminListColumn::make('id', 'ID', sortField: 'id', class: 'text-muted small', headerClass: 'text-muted'),
                AdminListColumn::make('title', 'Title', fn (DemoNote $note) => e($note->title), sortField: 'title'),
                AdminListColumn::make('body', 'Body', fn (DemoNote $note) => e(str($note->body)->limit(60)), class: 'small text-muted'),
            ],
            rowActions: [
                AdminListAction::make('edit', 'Edit', 'bi-pencil', fn (DemoNote $note) => route('admin.demo-notes.edit', $note), ability: 'update'),
                AdminListAction::make('delete', 'Delete', 'bi-trash', fn (DemoNote $note) => route('admin.demo-notes.destroy', $note), method: 'DELETE', ability: 'delete', confirm: 'Delete this note?', danger: true),
            ],
            searchFields: ['title', 'body'],
            searchPlaceholder: 'Search title or body...',
            defaultSort: 'title',
            defaultDirection: 'asc',
            defaultPerPage: 15,
        );
    }
}
