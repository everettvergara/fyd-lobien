<?php

namespace App\Modules\PageManager\Controllers;

use App\Framework\Admin\List\AdminBulkActionService;
use App\Http\Controllers\Controller;
use App\Modules\PageManager\Models\Page;
use App\Modules\PageManager\Requests\StorePageRequest;
use App\Modules\PageManager\Requests\UpdatePageRequest;
use App\Modules\PageManager\Services\PageAdminListService;
use App\Modules\PageManager\Services\PageBlockSyncService;
use App\Modules\PageManager\Services\PageManagerService;
use App\Modules\SEO\Services\SeoService;
use App\Modules\SEO\Services\SitemapService;
use App\Modules\Cache\Services\PublicCacheService;
use App\Services\ActivityLogger;
use App\Services\Media\MediaUsageService;
use App\Services\Public\PublicBlockRegistry;
use App\Services\PublishingService;
use App\Services\Theme\ThemeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function __construct(
        protected PageAdminListService $pageList,
        protected PageBlockSyncService $blockSync,
        protected PageManagerService $pages,
        protected PublishingService $publishing,
        protected SeoService $seo,
        protected MediaUsageService $usage,
        protected ThemeService $theme,
        protected PublicBlockRegistry $blockRegistry,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Page::class);

        return view('pagemanager::pages.index', [
            'list' => $this->pageList->result($request),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Page::class);

        return view('pagemanager::pages.create', $this->editorContext());
    }

    public function store(StorePageRequest $request): RedirectResponse
    {
        $page = Page::create([
            ...$request->safe()->except([...$this->seo->fieldKeys(), 'blocks']),
            'slug' => Page::slugFromPath($request->input('path')),
            'author_id' => $request->user()->id,
        ]);

        $page->saveSeo($this->seo->extract($request->validated()));
        $this->blockSync->syncPageBlocks($page, $request->input('blocks', []));
        $this->syncMediaUsage($page);
        $this->forgetCaches();
        ActivityLogger::log('pages', 'created', $page, ['title' => $page->title]);

        return redirect()->route('admin.pages.index')->with('success', 'Page created successfully.');
    }

    public function edit(Page $page): View
    {
        $this->authorize('update', $page);
        $page->load(['seoMeta.ogImage', 'featuredImage', 'blocks']);

        return view('pagemanager::pages.edit', array_merge($this->editorContext(), compact('page')));
    }

    public function update(UpdatePageRequest $request, Page $page): RedirectResponse
    {
        $page->update($request->safe()->except([...$this->seo->fieldKeys(), 'blocks']));
        $page->saveSeo($this->seo->extract($request->validated()));
        $this->blockSync->syncPageBlocks($page, $request->input('blocks', []));
        $this->syncMediaUsage($page);
        $this->forgetCaches();
        ActivityLogger::log('pages', 'updated', $page, ['title' => $page->title]);

        return redirect()->route('admin.pages.index')->with('success', 'Page updated successfully.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        $this->authorize('delete', $page);
        ActivityLogger::log('pages', 'deleted', $page, ['title' => $page->title]);
        $this->usage->removeModel($page);
        if ($page->seoMeta) {
            $this->usage->removeModel($page->seoMeta);
        }
        $page->delete();
        $this->forgetCaches();

        return redirect()->route('admin.pages.index')->with('success', 'Page deleted successfully.');
    }

    public function publish(Page $page): RedirectResponse
    {
        $this->authorize('publish', $page);
        $this->publishing->publish($page);
        $this->forgetCaches();
        ActivityLogger::log('pages', 'published', $page, ['title' => $page->title]);

        return back()->with('success', 'Page published successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function editorContext(): array
    {
        $master = \App\Modules\PageManager\Models\PageMaster::instance();

        return [
            'regions' => $this->theme->activeRegions(),
            'blockPalette' => $this->blockRegistry->paletteForAdmin(),
            'statuses' => \App\Enums\ContentStatus::cases(),
            'masterConfigured' => $master->is_configured,
        ];
    }

    protected function syncMediaUsage(Page $page): void
    {
        $this->usage->syncModel($page, 'pages', [
            'featured_image_id' => 'Featured Image',
        ]);

        if ($page->seoMeta) {
            $this->usage->syncModel($page->seoMeta, 'seo', [
                'og_image_id' => 'OG Image',
            ]);
        }
    }

    protected function forgetCaches(): void
    {
        SitemapService::forgetCache();
        app(PublicCacheService::class)->clearAll();
    }
}
