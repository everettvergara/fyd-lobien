<?php

namespace App\Modules\Content\Services;

use App\Enums\ContentStatus;
use App\Framework\Admin\List\AdminBulkAction;
use App\Framework\Admin\List\AdminListAction;
use App\Framework\Admin\List\AdminListColumn;
use App\Framework\Admin\List\AdminListDefinition;
use App\Framework\Admin\List\AdminListFilter;
use App\Framework\Admin\List\AdminListResult;
use App\Framework\Admin\List\AdminListService;
use App\Modules\Content\Models\Content;
use App\Services\ActivityLogger;
use App\Services\PublishingService;
use App\Support\ContentTypeRegistry;
use App\Support\OwnContentAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ContentAdminListService
{
    public function __construct(
        protected AdminListService $lists,
        protected PublishingService $publishing,
        protected ContentTypeRegistry $contentTypes,
    ) {}

    public function result(Request $request): AdminListResult
    {
        $query = Content::query()->with('author');

        if (OwnContentAccess::managesOwnContentOnly($request->user())) {
            $query->where('author_id', $request->user()->id);
        }

        return $this->lists->build($query, $this->definition(), $request);
    }

    public function definition(): AdminListDefinition
    {
        return new AdminListDefinition(
            id: 'content',
            title: 'Content',
            modelClass: Content::class,
            columns: $this->columns(),
            filters: $this->filters(),
            rowActions: $this->rowActions(),
            bulkActions: $this->bulkActions(),
            searchFields: ['title', 'slug'],
            searchPlaceholder: 'Search title or slug...',
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
            AdminListColumn::make('title', 'Title', fn (Content $content) => sprintf(
                '<a href="%s" class="text-decoration-none fw-medium">%s</a>',
                route('admin.content.show', $content),
                e($content->title),
            ), sortField: 'title', raw: true),
            AdminListColumn::make('content_type', 'Type', fn (Content $content) => $this->contentTypes->badgeHtml($content->content_type), sortField: 'content_type', class: 'small', raw: true),
            AdminListColumn::make('slug', 'URI', fn (Content $content) => '/'.$content->slug, sortField: 'slug', class: 'text-muted small'),
            AdminListColumn::make('status', 'Status', fn (Content $content) => view('components.admin.status-badge', ['status' => $content->status])->render(), sortField: 'status', raw: true),
            AdminListColumn::make('author', 'Author', fn (Content $content) => $content->author?->name ?? 'Unknown', class: 'small'),
            AdminListColumn::make('published_at', 'Published', fn (Content $content) => $content->published_at?->format('M j, Y') ?? 'Not published', sortField: 'published_at', class: 'text-muted small'),
            AdminListColumn::make('updated_at', 'Updated', fn (Content $content) => $content->updated_at->diffForHumans(), sortField: 'updated_at', class: 'text-muted small'),
        ];
    }

    protected function filters(): array
    {
        return [
            AdminListFilter::make(
                'content_type',
                'Type',
                'select',
                fn () => $this->contentTypes->options(),
                fn (Builder $query, string $value) => $this->contentTypes->has($value)
                    ? $query->where('content_type', $value)
                    : $query,
            ),
        ];
    }

    protected function rowActions(): array
    {
        return [
            AdminListAction::make('view', 'View', 'bi-eye', fn (Content $content) => route('admin.content.show', $content), ability: 'view'),
            AdminListAction::make('preview', 'Preview', 'bi-box-arrow-up-right', fn (Content $content) => route('admin.content.preview', $content), ability: 'view', newTab: true),
            AdminListAction::make('edit', 'Edit', 'bi-pencil', fn (Content $content) => route('admin.content.edit', $content), ability: 'update'),
            AdminListAction::make(
                'publish',
                'Publish',
                'bi-check-circle',
                fn (Content $content) => route('admin.content.publish', $content),
                method: 'POST',
                ability: 'publish',
                visible: fn (Content $content) => $content->status !== ContentStatus::Published,
            ),
            AdminListAction::make(
                'archive',
                'Archive',
                'bi-archive',
                fn (Content $content) => route('admin.content.archive', $content),
                method: 'POST',
                ability: 'update',
                visible: fn (Content $content) => $content->status !== ContentStatus::Archived,
            ),
            AdminListAction::make(
                'duplicate',
                'Duplicate',
                'bi-copy',
                fn (Content $content) => route('admin.content.duplicate', $content),
                method: 'POST',
                ability: 'create',
                abilityTarget: fn () => Content::class,
            ),
            AdminListAction::make(
                'delete',
                'Delete',
                'bi-trash',
                fn (Content $content) => route('admin.content.destroy', $content),
                method: 'DELETE',
                ability: 'delete',
                confirm: 'Delete this content?',
                danger: true,
            ),
        ];
    }

    protected function bulkActions(): array
    {
        return [
            AdminBulkAction::make('publish', 'Publish selected', 'publish', fn (Collection $items, Request $request) => $this->bulkPublish($items), 'Publish selected content?'),
            AdminBulkAction::make('archive', 'Archive selected', 'update', fn (Collection $items, Request $request) => $this->bulkArchive($items), 'Archive selected content?'),
            AdminBulkAction::make('delete', 'Delete selected', 'delete', fn (Collection $items, Request $request) => $this->bulkDelete($items), 'Delete selected content?', danger: true),
        ];
    }

    protected function bulkPublish(Collection $items): int
    {
        $items->each(fn (Content $content) => $this->publishing->publish($content, 'content'));

        return $items->count();
    }

    protected function bulkArchive(Collection $items): int
    {
        $items->each(fn (Content $content) => $this->publishing->archive($content, 'content'));

        return $items->count();
    }

    protected function bulkDelete(Collection $items): int
    {
        $items->each(function (Content $content) {
            ActivityLogger::log('content', 'deleted', $content, ['title' => $content->title]);
            $content->delete();
        });

        return $items->count();
    }
}
