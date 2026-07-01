<?php

namespace App\Modules\Pages\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Pages\Models\Page;
use App\Modules\Pages\Requests\StorePageRequest;
use App\Modules\Pages\Requests\UpdatePageRequest;
use App\Modules\Pages\Services\PageSectionService;
use App\Modules\SEO\Services\SeoService;
use App\Services\ActivityLogger;
use App\Services\PublishingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PageController extends Controller
{
    public function __construct(
        protected PageSectionService $pageSections,
        protected PublishingService $publishing,
        protected SeoService $seo,
    ) {}

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
        $statuses = \App\Enums\ContentStatus::cases();
        $components = $this->pageSections->componentTypes();

        return view('pages::pages.create', compact('pages', 'statuses', 'components'));
    }

    public function store(StorePageRequest $request): RedirectResponse
    {
        $page = Page::create([
            ...$request->safe()->except(array_merge(['sections'], $this->seo->fieldKeys())),
            'author_id' => $request->user()->id,
        ]);

        $this->pageSections->sync($page, $request->sections ?? []);
        $page->saveSeo($this->seo->extract($request->validated()));
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
        $page->load(['sections', 'seoMeta.ogImage', 'featuredImage']);
        $pages = Page::where('id', '!=', $page->id)->orderBy('title')->get();
        $statuses = \App\Enums\ContentStatus::cases();
        $components = $this->pageSections->componentTypes();

        return view('pages::pages.edit', compact('page', 'pages', 'statuses', 'components'));
    }

    public function update(UpdatePageRequest $request, Page $page): RedirectResponse
    {
        $page->update($request->safe()->except(array_merge(['sections'], $this->seo->fieldKeys())));
        $this->pageSections->sync($page, $request->sections ?? []);
        $page->saveSeo($this->seo->extract($request->validated()));
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
        $this->publishing->publish($page, 'pages');

        return back()->with('success', 'Page published successfully.');
    }

    public function archive(Page $page): RedirectResponse
    {
        $this->authorize('update', $page);
        $this->publishing->archive($page, 'pages');

        return back()->with('success', 'Page archived successfully.');
    }

    public function duplicate(Page $page): RedirectResponse
    {
        $this->authorize('create', Page::class);
        $page->load('sections', 'seoMeta');

        $newPage = $this->publishing->duplicate(
            $page,
            'pages',
            [
                'title' => $page->title.' (Copy)',
                'slug' => $this->publishing->generateCopySlug($page->slug),
                'author_id' => auth()->id(),
            ],
            function (Page $source, Page $duplicate) {
                foreach ($source->sections as $section) {
                    $duplicate->sections()->create($section->only(['component_type', 'sort_order', 'settings']));
                }
            }
        );

        return redirect()->route('admin.pages.edit', $newPage)->with('success', 'Page duplicated successfully.');
    }

    public function preview(Page $page): View
    {
        $this->authorize('view', $page);
        $page->load(['sections', 'featuredImage']);

        return view('pages::pages.preview', compact('page'));
    }
}
