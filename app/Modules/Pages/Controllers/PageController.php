<?php

namespace App\Modules\Pages\Controllers;

use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Modules\Pages\Models\Page;
use App\Modules\Pages\Models\PageSection;
use App\Modules\Pages\Requests\StorePageRequest;
use App\Modules\Pages\Requests\UpdatePageRequest;
use App\Services\ActivityLogger;
use App\Support\SeoFields;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PageController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Page::class);
        $pages = Page::with('author')->latest()->paginate(15);

        return view('pages::pages.index', compact('pages'));
    }

    public function create(): View
    {
        $this->authorize('create', Page::class);
        $pages = Page::orderBy('title')->get();
        $statuses = ContentStatus::cases();
        $components = ['hero_banner', 'feature_grid', 'cta', 'statistics', 'contact', 'faq'];

        return view('pages::pages.create', compact('pages', 'statuses', 'components'));
    }

    public function store(StorePageRequest $request): RedirectResponse
    {
        $page = Page::create([
            ...$request->safe()->except(['sections', 'seo_title', 'meta_description', 'meta_keywords', 'canonical_url', 'og_title', 'og_description', 'og_image_id', 'robots']),
            'author_id' => $request->user()->id,
        ]);

        $this->syncSections($page, $request->sections ?? []);
        $page->saveSeo(SeoFields::extract($request->validated()));
        ActivityLogger::log('pages', 'created', $page, ['title' => $page->title]);

        return redirect()->route('admin.pages.index')->with('success', 'Page created successfully.');
    }

    public function show(Page $page): View
    {
        $this->authorize('view', $page);
        $page->load(['author', 'sections', 'seoMeta', 'featuredImage']);

        return view('pages::pages.show', compact('page'));
    }

    public function edit(Page $page): View
    {
        $this->authorize('update', $page);
        $page->load(['sections', 'seoMeta']);
        $pages = Page::where('id', '!=', $page->id)->orderBy('title')->get();
        $statuses = ContentStatus::cases();
        $components = ['hero_banner', 'feature_grid', 'cta', 'statistics', 'contact', 'faq'];

        return view('pages::pages.edit', compact('page', 'pages', 'statuses', 'components'));
    }

    public function update(UpdatePageRequest $request, Page $page): RedirectResponse
    {
        $page->update($request->safe()->except(['sections', 'seo_title', 'meta_description', 'meta_keywords', 'canonical_url', 'og_title', 'og_description', 'og_image_id', 'robots']));
        $this->syncSections($page, $request->sections ?? []);
        $page->saveSeo(SeoFields::extract($request->validated()));
        ActivityLogger::log('pages', 'updated', $page, ['title' => $page->title]);

        return redirect()->route('admin.pages.index')->with('success', 'Page updated successfully.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        $this->authorize('delete', $page);
        ActivityLogger::log('pages', 'deleted', $page, ['title' => $page->title]);
        $page->delete();

        return redirect()->route('admin.pages.index')->with('success', 'Page deleted successfully.');
    }

    public function publish(Page $page): RedirectResponse
    {
        $this->authorize('publish', $page);
        $page->update(['status' => ContentStatus::Published, 'published_at' => now()]);
        ActivityLogger::log('pages', 'published', $page);

        return back()->with('success', 'Page published successfully.');
    }

    public function archive(Page $page): RedirectResponse
    {
        $this->authorize('update', $page);
        $page->update(['status' => ContentStatus::Archived]);
        ActivityLogger::log('pages', 'updated', $page, ['action' => 'archived']);

        return back()->with('success', 'Page archived successfully.');
    }

    public function duplicate(Page $page): RedirectResponse
    {
        $this->authorize('create', Page::class);
        $page->load('sections', 'seoMeta');
        $newPage = $page->replicate(['slug', 'published_at']);
        $newPage->title = $page->title.' (Copy)';
        $newPage->slug = $page->slug.'-copy-'.Str::random(4);
        $newPage->status = ContentStatus::Draft;
        $newPage->published_at = null;
        $newPage->author_id = auth()->id();
        $newPage->save();

        foreach ($page->sections as $section) {
            $newPage->sections()->create($section->only(['component_type', 'sort_order', 'settings']));
        }

        if ($page->seoMeta) {
            $newPage->saveSeo($page->seoMeta->only([
                'seo_title', 'meta_description', 'meta_keywords', 'canonical_url',
                'og_title', 'og_description', 'og_image_id', 'robots',
            ]));
        }

        ActivityLogger::log('pages', 'created', $newPage, ['duplicated_from' => $page->id]);

        return redirect()->route('admin.pages.edit', $newPage)->with('success', 'Page duplicated successfully.');
    }

    public function preview(Page $page): View
    {
        $this->authorize('view', $page);
        $page->load(['sections', 'featuredImage']);

        return view('pages::pages.preview', compact('page'));
    }

    protected function syncSections(Page $page, array $sections): void
    {
        $page->sections()->delete();
        foreach ($sections as $index => $section) {
            $page->sections()->create([
                'component_type' => $section['component_type'],
                'sort_order' => $index,
                'settings' => $section['settings'] ?? [],
            ]);
        }
    }
}
