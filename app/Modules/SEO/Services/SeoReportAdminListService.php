<?php

namespace App\Modules\SEO\Services;

use App\Enums\ContentStatus;
use App\Enums\SitemapChangeFrequency;
use App\Framework\Admin\List\AdminListAction;
use App\Framework\Admin\List\AdminListColumn;
use App\Framework\Admin\List\AdminListDefinition;
use App\Framework\Admin\List\AdminListFilter;
use App\Framework\Admin\List\AdminListResult;
use App\Framework\Admin\List\AdminListService;
use App\Modules\PageManager\Models\Page;
use App\Services\SettingsService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SeoReportAdminListService
{
    public function __construct(
        protected AdminListService $lists,
        protected SettingsService $settings,
    ) {}

    public function result(Request $request): AdminListResult
    {
        $query = Page::query()->with('seoMeta');

        return $this->lists->build($query, $this->definition(), $request);
    }

    public function definition(): AdminListDefinition
    {
        return new AdminListDefinition(
            id: 'seo-report',
            title: 'SEO Report',
            modelClass: Page::class,
            columns: $this->columns(),
            filters: $this->filters(),
            rowActions: $this->rowActions(),
            searchQuery: fn (Builder $query, string $search) => $query->where(function (Builder $query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('path', 'like', "%{$search}%")
                    ->orWhereHas('seoMeta', function (Builder $query) use ($search) {
                        $query->where('seo_title', 'like', "%{$search}%")
                            ->orWhere('meta_description', 'like', "%{$search}%");
                    });
            }),
            searchPlaceholder: 'Search title, path, or SEO fields...',
            defaultSort: 'updated_at',
            defaultDirection: 'desc',
            defaultPerPage: 25,
            selectable: false,
        );
    }

    protected function columns(): array
    {
        return [
            AdminListColumn::make('title', 'Title', fn (Page $page) => sprintf(
                '<a href="%s" class="text-decoration-none fw-medium">%s</a>',
                route('admin.pages.edit', $page),
                e($page->title),
            ), sortField: 'title', raw: true),
            AdminListColumn::make('path', 'Path', fn (Page $page) => e($page->path), sortField: 'path', class: 'small font-monospace'),
            AdminListColumn::make('status', 'Status', fn (Page $page) => view('components.admin.status-badge', ['status' => $page->status])->render(), sortField: 'status', raw: true),
            AdminListColumn::make('seo_title', 'SEO Title', fn (Page $page) => $this->seoTitleColumn($page), class: 'small', raw: true),
            AdminListColumn::make('meta_description', 'Meta Description', fn (Page $page) => $this->truncate($page->seoMeta?->meta_description), class: 'small text-muted'),
            AdminListColumn::make('robots', 'Robots', fn (Page $page) => e($page->seoMeta?->robots ?? 'index,follow'), class: 'small'),
            AdminListColumn::make('sitemap_include', 'Sitemap', fn (Page $page) => $this->sitemapIncludeBadge($page), class: 'small', raw: true),
        ];
    }

    protected function filters(): array
    {
        return [
            AdminListFilter::make(
                'status',
                'Status',
                'select',
                fn () => collect(ContentStatus::cases())->mapWithKeys(fn (ContentStatus $status) => [$status->value => $status->label()])->all(),
                fn (Builder $query, string $value) => $query->where('status', $value),
            ),
        ];
    }

    protected function rowActions(): array
    {
        return [
            AdminListAction::make('edit', 'Edit', 'bi-pencil', fn (Page $page) => route('admin.pages.edit', $page), ability: 'update'),
        ];
    }

    protected function seoTitleColumn(Page $page): string
    {
        $title = $page->seoMeta?->seo_title;

        if (! $title) {
            return '<span class="badge bg-warning-subtle text-warning">Missing</span>';
        }

        return e($this->truncate($title, 50));
    }

    protected function sitemapIncludeBadge(Page $page): string
    {
        $included = $page->seoMeta?->sitemap_include ?? true;

        return $included
            ? '<span class="badge bg-success-subtle text-success">Included</span>'
            : '<span class="badge bg-secondary-subtle text-secondary">Excluded</span>';
    }

    protected function truncate(?string $value, int $limit = 80): string
    {
        if (! $value) {
            return '—';
        }

        return Str::limit($value, $limit);
    }
}
