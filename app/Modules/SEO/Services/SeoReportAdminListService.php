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
use App\Modules\Content\Models\Content;
use App\Services\SettingsService;
use App\Support\ContentTypeRegistry;
use App\Support\OwnContentAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SeoReportAdminListService
{
    public function __construct(
        protected AdminListService $lists,
        protected ContentTypeRegistry $contentTypes,
        protected SettingsService $settings,
    ) {}

    public function result(Request $request): AdminListResult
    {
        $query = Content::query()->with(['seoMeta', 'author']);

        if (OwnContentAccess::managesOwnContentOnly($request->user())) {
            $query->where('author_id', $request->user()->id);
        }

        return $this->lists->build($query, $this->definition(), $request);
    }

    public function definition(): AdminListDefinition
    {
        return new AdminListDefinition(
            id: 'seo-report',
            title: 'SEO Report',
            modelClass: Content::class,
            columns: $this->columns(),
            filters: $this->filters(),
            rowActions: $this->rowActions(),
            searchQuery: fn (Builder $query, string $search) => $query->where(function (Builder $query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhereHas('seoMeta', function (Builder $query) use ($search) {
                        $query->where('seo_title', 'like', "%{$search}%")
                            ->orWhere('meta_description', 'like', "%{$search}%");
                    });
            }),
            searchPlaceholder: 'Search title, slug, or SEO fields...',
            defaultSort: 'updated_at',
            defaultDirection: 'desc',
            defaultPerPage: 25,
            selectable: false,
        );
    }

    protected function columns(): array
    {
        return [
            AdminListColumn::make('title', 'Title', fn (Content $content) => sprintf(
                '<a href="%s" class="text-decoration-none fw-medium">%s</a>',
                route('admin.content.edit', $content),
                e($content->title),
            ), sortField: 'title', raw: true),
            AdminListColumn::make('content_type', 'Type', fn (Content $content) => $this->contentTypes->badgeHtml($content->content_type), sortField: 'content_type', class: 'small', raw: true),
            AdminListColumn::make('status', 'Status', fn (Content $content) => view('components.admin.status-badge', ['status' => $content->status])->render(), sortField: 'status', raw: true),
            AdminListColumn::make('seo_title', 'SEO Title', fn (Content $content) => $this->seoTitleColumn($content), class: 'small', raw: true),
            AdminListColumn::make('meta_description', 'Meta Description', fn (Content $content) => $this->truncate($content->seoMeta?->meta_description), class: 'small text-muted'),
            AdminListColumn::make('meta_keywords', 'Meta Keywords', fn (Content $content) => $this->truncate($content->seoMeta?->meta_keywords, 40), class: 'small text-muted'),
            AdminListColumn::make('canonical_url', 'Canonical URL', fn (Content $content) => $this->truncate($content->seoMeta?->canonical_url, 40), class: 'small text-muted'),
            AdminListColumn::make('robots', 'Robots', fn (Content $content) => e($content->seoMeta?->robots ?? 'index,follow'), class: 'small'),
            AdminListColumn::make('sitemap_include', 'Sitemap', fn (Content $content) => $this->sitemapIncludeBadge($content), class: 'small', raw: true),
            AdminListColumn::make('sitemap_changefreq', 'Change Freq', fn (Content $content) => e($this->effectiveChangefreqLabel($content)), class: 'small text-muted'),
            AdminListColumn::make('sitemap_priority', 'Priority', fn (Content $content) => e($this->effectivePriority($content)), class: 'small text-muted'),
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
            AdminListFilter::make(
                'status',
                'Status',
                'select',
                fn () => collect(ContentStatus::cases())->mapWithKeys(fn (ContentStatus $status) => [$status->value => $status->label()])->all(),
                fn (Builder $query, string $value) => $query->where('status', $value),
            ),
            AdminListFilter::make(
                'sitemap_include',
                'Sitemap',
                'select',
                ['included' => 'Included', 'excluded' => 'Excluded'],
                function (Builder $query, string $value) {
                    if ($value === 'included') {
                        $query->where(function (Builder $query) {
                            $query->whereDoesntHave('seoMeta')
                                ->orWhereHas('seoMeta', fn (Builder $query) => $query->where('sitemap_include', true));
                        });
                    }

                    if ($value === 'excluded') {
                        $query->whereHas('seoMeta', fn (Builder $query) => $query->where('sitemap_include', false));
                    }
                },
            ),
            AdminListFilter::make(
                'robots',
                'Robots',
                'select',
                ['indexable' => 'Indexable', 'noindex' => 'Noindex'],
                function (Builder $query, string $value) {
                    if ($value === 'indexable') {
                        $query->where(function (Builder $query) {
                            $query->whereDoesntHave('seoMeta')
                                ->orWhereHas('seoMeta', fn (Builder $query) => $query->where('robots', 'not like', '%noindex%'));
                        });
                    }

                    if ($value === 'noindex') {
                        $query->whereHas('seoMeta', fn (Builder $query) => $query->where('robots', 'like', '%noindex%'));
                    }
                },
            ),
            AdminListFilter::make(
                'seo_completeness',
                'SEO Quality',
                'select',
                ['missing_title' => 'Missing title', 'missing_description' => 'Missing description'],
                function (Builder $query, string $value) {
                    if ($value === 'missing_title') {
                        $query->where(function (Builder $query) {
                            $query->whereDoesntHave('seoMeta')
                                ->orWhereHas('seoMeta', fn (Builder $query) => $query->whereNull('seo_title')->orWhere('seo_title', ''));
                        });
                    }

                    if ($value === 'missing_description') {
                        $query->where(function (Builder $query) {
                            $query->whereDoesntHave('seoMeta')
                                ->orWhereHas('seoMeta', fn (Builder $query) => $query->whereNull('meta_description')->orWhere('meta_description', ''));
                        });
                    }
                },
            ),
        ];
    }

    protected function rowActions(): array
    {
        return [
            AdminListAction::make('edit', 'Edit', 'bi-pencil', fn (Content $content) => route('admin.content.edit', $content), ability: 'update'),
            AdminListAction::make('view', 'View', 'bi-eye', fn (Content $content) => route('admin.content.show', $content), ability: 'view'),
        ];
    }

    protected function seoTitleColumn(Content $content): string
    {
        $title = $content->seoMeta?->seo_title;

        if (! $title) {
            return '<span class="badge bg-warning-subtle text-warning">Missing</span>';
        }

        return e($this->truncate($title, 50));
    }

    protected function sitemapIncludeBadge(Content $content): string
    {
        $included = $content->seoMeta?->sitemap_include ?? true;

        if ($included) {
            return '<span class="badge bg-success-subtle text-success">Included</span>';
        }

        return '<span class="badge bg-secondary-subtle text-secondary">Excluded</span>';
    }

    protected function effectiveChangefreqLabel(Content $content): string
    {
        $override = $content->seoMeta?->sitemap_changefreq;
        if ($override) {
            return SitemapChangeFrequency::tryFrom($override)?->label() ?? $override;
        }

        $key = $content->content_type === 'article'
            ? 'default_changefreq_article'
            : 'default_changefreq_page';

        $default = (string) $this->settings->get('seo', $key, 'monthly');

        return SitemapChangeFrequency::tryFrom($default)?->label() ?? $default.' (default)';
    }

    protected function effectivePriority(Content $content): string
    {
        $priority = $content->seoMeta?->sitemap_priority;

        if ($priority !== null) {
            return number_format((float) $priority, 1, '.', '');
        }

        return number_format((float) $this->settings->get('seo', 'default_priority', '0.5'), 1, '.', '').' (default)';
    }

    protected function truncate(?string $value, int $limit = 80): string
    {
        if (! $value) {
            return '—';
        }

        return Str::limit($value, $limit);
    }
}
