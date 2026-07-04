<?php

namespace App\Modules\ContentBlocks\Services;

use App\Enums\ContentStatus;
use App\Framework\Admin\List\AdminBulkAction;
use App\Framework\Admin\List\AdminListAction;
use App\Framework\Admin\List\AdminListColumn;
use App\Framework\Admin\List\AdminListDefinition;
use App\Framework\Admin\List\AdminListFilter;
use App\Framework\Admin\List\AdminListResult;
use App\Framework\Admin\List\AdminListService;
use App\Modules\ContentBlocks\Enums\ContentBlockFormatter;
use App\Modules\ContentBlocks\Models\ContentBlock;
use App\Services\ActivityLogger;
use App\Support\AdminIcon;
use App\Support\ContentTypeRegistry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ContentBlockAdminListService
{
    public function __construct(
        protected AdminListService $lists,
        protected ContentBlockService $contentBlocks,
        protected ContentBlockRenderingService $rendering,
    ) {}

    public function result(Request $request): AdminListResult
    {
        $query = ContentBlock::query();

        return $this->lists->build($query, $this->definition(), $request);
    }

    public function definition(): AdminListDefinition
    {
        return new AdminListDefinition(
            id: 'content_blocks',
            title: 'Content Blocks',
            modelClass: ContentBlock::class,
            columns: $this->columns(),
            filters: $this->filters(),
            rowActions: $this->rowActions(),
            bulkActions: $this->bulkActions(),
            searchFields: ['name', 'key'],
            searchPlaceholder: 'Search name or key...',
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
            AdminListColumn::make('icon', 'Icon', fn (ContentBlock $block) => sprintf(
                '<i class="%s" aria-hidden="true"></i>',
                e(AdminIcon::solid($block->icon)),
            ), class: 'small', raw: true),
            AdminListColumn::make('name', 'Name', fn (ContentBlock $block) => sprintf(
                '<a href="%s" class="text-decoration-none fw-medium">%s</a>',
                route('admin.content-blocks.edit', $block),
                e($block->name),
            ), sortField: 'name', raw: true),
            AdminListColumn::make('key', 'Key', fn (ContentBlock $block) => '<code>'.e($block->key).'</code>', sortField: 'key', class: 'small', raw: true),
            AdminListColumn::make('content_types', 'Content Types', fn (ContentBlock $block) => collect($block->content_types ?? [])
                ->map(fn (string $type) => app(ContentTypeRegistry::class)->badgeHtml($type))
                ->implode(' '), raw: true, class: 'small'),
            AdminListColumn::make('formatter', 'Formatter', fn (ContentBlock $block) => $block->formatter->label(), sortField: 'formatter', class: 'small'),
            AdminListColumn::make('items', 'Items', fn (ContentBlock $block) => $block->pagination_enabled
                ? e((string) $block->items_per_page).' / page'
                : e((string) $block->items_per_page).' max', class: 'small text-muted'),
            AdminListColumn::make('status', 'Status', fn (ContentBlock $block) => view('components.admin.status-badge', ['status' => $block->status])->render(), sortField: 'status', raw: true),
            AdminListColumn::make('updated_at', 'Updated', fn (ContentBlock $block) => $block->updated_at->diffForHumans(), sortField: 'updated_at', class: 'text-muted small'),
        ];
    }

    protected function filters(): array
    {
        return [
            AdminListFilter::make('status', 'Status', 'select', $this->statusOptions(), fn (Builder $query, string $value) => $query->where('status', $value)),
            AdminListFilter::make('formatter', 'Formatter', 'select', ContentBlockFormatter::options(), fn (Builder $query, string $value) => $query->where('formatter', $value)),
        ];
    }

    protected function rowActions(): array
    {
        return [
            AdminListAction::make('edit', 'Edit', 'bi-pencil', fn (ContentBlock $block) => route('admin.content-blocks.edit', $block), ability: 'update'),
            AdminListAction::make(
                'delete',
                'Delete',
                'bi-trash',
                fn (ContentBlock $block) => route('admin.content-blocks.destroy', $block),
                method: 'DELETE',
                ability: 'delete',
                confirm: 'Delete this content block?',
                danger: true,
            ),
        ];
    }

    protected function bulkActions(): array
    {
        return [
            AdminBulkAction::make('publish', 'Publish selected', 'publish', fn (Collection $blocks) => $this->bulkStatus($blocks, ContentStatus::Published)),
            AdminBulkAction::make('archive', 'Archive selected', 'archive', fn (Collection $blocks) => $this->bulkStatus($blocks, ContentStatus::Archived)),
            AdminBulkAction::make('delete', 'Delete selected', 'delete', fn (Collection $blocks, Request $request) => $this->bulkDelete($blocks), 'Delete selected content blocks?', danger: true),
        ];
    }

    protected function bulkStatus(Collection $blocks, ContentStatus $status): int
    {
        $blocks->each(function (ContentBlock $block) use ($status) {
            $block->update(['status' => $status]);
            ActivityLogger::log('content_blocks', $status === ContentStatus::Published ? 'published' : 'archived', $block);
            $this->rendering->forgetCache($block->key);
        });

        return $blocks->count();
    }

    protected function bulkDelete(Collection $blocks): int
    {
        $blocks->each(function (ContentBlock $block) {
            ActivityLogger::log('content_blocks', 'deleted', $block);
            $this->contentBlocks->delete($block);
        });

        return $blocks->count();
    }

    /**
     * @return array<string, string>
     */
    protected function statusOptions(): array
    {
        return collect(ContentStatus::cases())
            ->mapWithKeys(fn (ContentStatus $status) => [$status->value => $status->label()])
            ->all();
    }
}
