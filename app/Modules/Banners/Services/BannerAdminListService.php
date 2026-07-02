<?php

namespace App\Modules\Banners\Services;

use App\Enums\ContentStatus;
use App\Framework\Admin\List\AdminBulkAction;
use App\Framework\Admin\List\AdminListAction;
use App\Framework\Admin\List\AdminListColumn;
use App\Framework\Admin\List\AdminListDefinition;
use App\Framework\Admin\List\AdminListFilter;
use App\Framework\Admin\List\AdminListResult;
use App\Framework\Admin\List\AdminListService;
use App\Modules\Banners\Models\Banner;
use App\Modules\Banners\Models\BannerTemplate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class BannerAdminListService
{
    public function __construct(
        protected AdminListService $lists,
        protected BannerService $banners,
    ) {}

    public function result(Request $request): AdminListResult
    {
        $query = Banner::query()->with(['template', 'mediaAssignments.media', 'desktopImage']);

        return $this->lists->build($query, $this->definition(), $request);
    }

    public function definition(): AdminListDefinition
    {
        return new AdminListDefinition(
            id: 'banners',
            title: 'Banners',
            modelClass: Banner::class,
            columns: $this->columns(),
            filters: $this->filters(),
            rowActions: $this->rowActions(),
            bulkActions: $this->bulkActions(),
            searchFields: ['name', 'title', 'key'],
            searchPlaceholder: 'Search name, key, or title...',
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
            AdminListColumn::make('preview', 'Preview', fn (Banner $banner) => $this->preview($banner), raw: true, class: 'small'),
            AdminListColumn::make('name', 'Banner Title', fn (Banner $banner) => sprintf(
                '<a href="%s" class="text-decoration-none fw-medium">%s</a><div class="text-muted small">%s</div>',
                route('admin.banners.edit', $banner),
                e($banner->name),
                e($banner->title ?: 'Untitled content'),
            ), sortField: 'name', raw: true),
            AdminListColumn::make('key', 'Key', fn (Banner $banner) => '<code>'.e($banner->key).'</code>', sortField: 'key', class: 'small', raw: true),
            AdminListColumn::make('template', 'Template', fn (Banner $banner) => $banner->template?->name ?? $banner->type?->label() ?? 'Default', sortField: 'template_id', class: 'small'),
            AdminListColumn::make('status', 'Status', fn (Banner $banner) => view('components.admin.status-badge', ['status' => $banner->status])->render(), sortField: 'status', raw: true),
            AdminListColumn::make('sort_order', 'Sort Order', 'sort_order', sortField: 'sort_order', class: 'small text-muted'),
            AdminListColumn::make('updated_at', 'Last Updated', fn (Banner $banner) => $banner->updated_at->diffForHumans(), sortField: 'updated_at', class: 'text-muted small'),
        ];
    }

    protected function filters(): array
    {
        return [
            AdminListFilter::make('status', 'Status', 'select', $this->statusOptions(), fn (Builder $query, string $value) => $query->where('status', $value)),
            AdminListFilter::make('template_id', 'Template', 'select', fn () => BannerTemplate::query()->orderBy('sort_order')->pluck('name', 'id')->all(), fn (Builder $query, string $value) => $query->where('template_id', $value)),
        ];
    }

    protected function rowActions(): array
    {
        return [
            AdminListAction::make('edit', 'Edit', 'bi-pencil', fn (Banner $banner) => route('admin.banners.edit', $banner), ability: 'update'),
            AdminListAction::make(
                'preview',
                'Preview',
                'bi-eye',
                fn (Banner $banner) => route('admin.banners.preview', $banner),
                ability: 'view',
            ),
            AdminListAction::make(
                'publish',
                'Publish',
                'bi-check-circle',
                fn (Banner $banner) => route('admin.banners.publish', $banner),
                method: 'POST',
                ability: 'publish',
                visible: fn (Banner $banner) => $banner->status !== ContentStatus::Published,
            ),
            AdminListAction::make(
                'unpublish',
                'Unpublish',
                'bi-pause-circle',
                fn (Banner $banner) => route('admin.banners.unpublish', $banner),
                method: 'POST',
                ability: 'publish',
                visible: fn (Banner $banner) => $banner->status === ContentStatus::Published,
            ),
            AdminListAction::make(
                'archive',
                'Archive',
                'bi-archive',
                fn (Banner $banner) => route('admin.banners.archive', $banner),
                method: 'POST',
                ability: 'archive',
                visible: fn (Banner $banner) => $banner->status !== ContentStatus::Archived,
            ),
            AdminListAction::make(
                'duplicate',
                'Duplicate',
                'bi-copy',
                fn (Banner $banner) => route('admin.banners.duplicate', $banner),
                method: 'POST',
                ability: 'create',
                abilityTarget: fn () => Banner::class,
            ),
            AdminListAction::make(
                'delete',
                'Delete',
                'bi-trash',
                fn (Banner $banner) => route('admin.banners.destroy', $banner),
                method: 'DELETE',
                ability: 'delete',
                confirm: 'Delete this banner?',
                danger: true,
            ),
        ];
    }

    protected function bulkActions(): array
    {
        return [
            AdminBulkAction::make('publish', 'Publish selected', 'publish', fn (Collection $banners, Request $request) => $this->bulkPublish($banners), 'Publish selected banners?'),
            AdminBulkAction::make('unpublish', 'Unpublish selected', 'publish', fn (Collection $banners, Request $request) => $this->bulkUnpublish($banners), 'Unpublish selected banners?'),
            AdminBulkAction::make('archive', 'Archive selected', 'archive', fn (Collection $banners, Request $request) => $this->bulkArchive($banners), 'Archive selected banners?'),
            AdminBulkAction::make('duplicate', 'Duplicate selected', null, fn (Collection $banners, Request $request) => $this->bulkDuplicate($banners), 'Duplicate selected banners?'),
            AdminBulkAction::make('delete', 'Delete selected', 'delete', fn (Collection $banners, Request $request) => $this->bulkDelete($banners), 'Delete selected banners?', danger: true),
        ];
    }

    protected function bulkPublish(Collection $banners): int
    {
        $banners->each(fn (Banner $banner) => $this->banners->publish($banner));

        return $banners->count();
    }

    protected function bulkUnpublish(Collection $banners): int
    {
        $banners->each(fn (Banner $banner) => $this->banners->unpublish($banner));

        return $banners->count();
    }

    protected function bulkArchive(Collection $banners): int
    {
        $banners->each(fn (Banner $banner) => $this->banners->archive($banner));

        return $banners->count();
    }

    protected function bulkDuplicate(Collection $banners): int
    {
        $banners->each(fn (Banner $banner) => $this->banners->duplicate($banner));

        return $banners->count();
    }

    protected function bulkDelete(Collection $banners): int
    {
        $banners->each(fn (Banner $banner) => $this->banners->delete($banner));

        return $banners->count();
    }

    protected function statusOptions(): array
    {
        return collect(ContentStatus::cases())->mapWithKeys(fn (ContentStatus $status) => [$status->value => $status->label()])->all();
    }

    protected function preview(Banner $banner): string
    {
        $media = $banner->mediaAssignments->first()?->media ?? $banner->desktopImage;

        if (! $media) {
            return '<span class="badge bg-light text-muted border">No media</span>';
        }

        return sprintf(
            '<img src="%s" alt="" class="rounded object-fit-cover border" style="width:72px;height:42px;">',
            e($media->variantUrl('thumbnail') ?? $media->url()),
        );
    }
}
