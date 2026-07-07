<?php

namespace App\Modules\Content\Controllers;

use App\Framework\Admin\List\AdminBulkActionService;
use App\Http\Controllers\Controller;
use App\Modules\Content\Models\Content;
use App\Modules\Content\Requests\StoreContentRequest;
use App\Modules\Content\Requests\UpdateContentRequest;
use App\Modules\Content\Services\ContentAdminListService;
use App\Modules\Cache\Services\PublicCacheService;
use App\Modules\SEO\Services\SitemapService;
use App\Services\ActivityLogger;
use App\Services\Media\MediaUsageService;
use App\Services\PublishingService;
use App\Support\ContentTypeRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContentController extends Controller
{
    public function __construct(
        protected ContentAdminListService $contentList,
        protected PublishingService $publishing,
        protected MediaUsageService $usage,
        protected ContentTypeRegistry $contentTypes,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Content::class);

        return view('content::content.index', [
            'list' => $this->contentList->result($request),
            'contentTypes' => $this->contentTypes,
        ]);
    }

    public function bulk(Request $request, AdminBulkActionService $bulkActions): RedirectResponse
    {
        $this->authorize('viewAny', Content::class);

        $count = $bulkActions->execute($this->contentList->definition(), $request);

        return back()->with('success', "{$count} content item(s) updated successfully.");
    }

    public function create(): View
    {
        $this->authorize('create', Content::class);
        $statuses = \App\Enums\ContentStatus::cases();
        $contentTypeOptions = $this->contentTypes->options();
        $contentTypeDefinitions = $this->contentTypes->all();
        $defaultContentType = request('content_type');
        if ($defaultContentType && ! $this->contentTypes->has($defaultContentType)) {
            $defaultContentType = null;
        }

        return view('content::content.create', compact('statuses', 'contentTypeOptions', 'contentTypeDefinitions', 'defaultContentType'));
    }

    public function store(StoreContentRequest $request): RedirectResponse
    {
        $content = Content::create([
            ...$request->safe()->except(['gallery_media_ids']),
            'author_id' => $request->user()->id,
        ]);

        $content->load('featuredImage');
        $this->syncGallery($content, $request->input('gallery_media_ids', []));
        $this->syncMediaUsage($content);
        $this->forgetSitemapCache();
        ActivityLogger::log('content', 'created', $content, ['title' => $content->title]);

        return redirect()->route('admin.content.index')->with('success', 'Content created successfully.');
    }

    public function show(Content $content): View
    {
        $this->authorize('view', $content);
        $content->load(['author', 'featuredImage', 'attachment']);

        return view('content::content.show', compact('content'));
    }

    public function edit(Content $content): View
    {
        $this->authorize('update', $content);
        $content->load(['featuredImage', 'galleryImages', 'attachment']);
        $statuses = \App\Enums\ContentStatus::cases();
        $contentTypeOptions = $this->contentTypes->options();
        $contentTypeDefinitions = $this->contentTypes->all();

        return view('content::content.edit', compact('content', 'statuses', 'contentTypeOptions', 'contentTypeDefinitions'));
    }

    public function update(UpdateContentRequest $request, Content $content): RedirectResponse
    {
        $content->update($request->safe()->except(['gallery_media_ids']));
        $content->load('featuredImage');
        $this->syncGallery($content, $request->input('gallery_media_ids', []));
        $this->syncMediaUsage($content);
        $this->forgetSitemapCache();
        ActivityLogger::log('content', 'updated', $content, ['title' => $content->title]);

        return redirect()->route('admin.content.index')->with('success', 'Content updated successfully.');
    }

    public function destroy(Content $content): RedirectResponse
    {
        $this->authorize('delete', $content);
        ActivityLogger::log('content', 'deleted', $content, ['title' => $content->title]);
        $this->usage->removeModel($content);
        $content->delete();
        $this->forgetSitemapCache();

        return redirect()->route('admin.content.index')->with('success', 'Content deleted successfully.');
    }

    public function publish(Content $content): RedirectResponse
    {
        $this->authorize('publish', $content);
        $this->publishing->publish($content, 'content');
        $this->forgetSitemapCache();

        return back()->with('success', 'Content published successfully.');
    }

    public function archive(Content $content): RedirectResponse
    {
        $this->authorize('update', $content);
        $this->publishing->archive($content, 'content');
        $this->forgetSitemapCache();

        return back()->with('success', 'Content archived successfully.');
    }

    public function duplicate(Content $content): RedirectResponse
    {
        $this->authorize('create', Content::class);
        $content->load('featuredImage');

        $duplicate = $this->publishing->duplicate(
            $content,
            'content',
            [
                'title' => $content->title.' (Copy)',
                'slug' => $this->publishing->generateCopySlug($content->slug),
                'author_id' => auth()->id(),
            ],
        );

        return redirect()->route('admin.content.edit', $duplicate)->with('success', 'Content duplicated successfully.');
    }

    public function preview(Content $content): View
    {
        $this->authorize('view', $content);
        $content->load(['featuredImage', 'attachment']);

        return view('content::content.preview', compact('content'));
    }

    protected function syncGallery(Content $content, ?array $mediaIds): void
    {
        $mediaIds = array_values(array_filter($mediaIds ?? []));

        $sync = [];
        foreach ($mediaIds as $index => $mediaId) {
            $sync[(int) $mediaId] = ['sort_order' => $index];
        }

        $content->galleryImages()->sync($sync);

        $featuredImageId = $sync !== [] ? (int) array_key_first($sync) : null;

        if ($content->featured_image_id !== $featuredImageId) {
            $content->update(['featured_image_id' => $featuredImageId]);
        }
    }

    protected function syncMediaUsage(Content $content): void
    {
        $content->loadMissing('galleryImages');

        $this->usage->syncModel($content, 'content', [
            'featured_image_id' => 'Featured Image',
            'attachment_id' => 'PDF Attachment',
        ]);

        $this->usage->syncRelatedMedia(
            $content,
            'content',
            'gallery_media',
            $content->galleryImages->pluck('id')->all(),
            'Gallery Image',
        );
    }

    protected function forgetSitemapCache(): void
    {
        SitemapService::forgetCache();
        app(PublicCacheService::class)->clearAll();
    }
}
