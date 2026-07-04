<?php

namespace App\Modules\ContentBlocks\Controllers;

use App\Framework\Admin\List\AdminBulkActionService;
use App\Http\Controllers\Controller;
use App\Modules\Cache\Services\PublicCacheService;
use App\Modules\ContentBlocks\Models\ContentBlock;
use App\Modules\ContentBlocks\Requests\PreviewContentBlockRequest;
use App\Modules\ContentBlocks\Requests\StoreContentBlockRequest;
use App\Modules\ContentBlocks\Requests\UpdateContentBlockRequest;
use App\Modules\ContentBlocks\Services\ContentBlockAdminListService;
use App\Modules\ContentBlocks\Services\ContentBlockPreviewService;
use App\Modules\ContentBlocks\Services\ContentBlockService;
use App\Modules\ContentBlocks\Support\ContentBlockFieldRegistry;
use App\Modules\ContentBlocks\Support\ContentBlockFilterOperators;
use App\Services\ActivityLogger;
use App\Support\ContentTypeRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContentBlockController extends Controller
{
    public function __construct(
        protected ContentBlockAdminListService $list,
        protected ContentBlockService $contentBlocks,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', ContentBlock::class);

        return view('contentblocks::content-blocks.index', [
            'list' => $this->list->result($request),
        ]);
    }

    public function bulk(Request $request, AdminBulkActionService $bulkActions): RedirectResponse
    {
        $this->authorize('viewAny', ContentBlock::class);

        $count = $bulkActions->execute($this->list->definition(), $request);
        app(PublicCacheService::class)->clearAll();

        return back()->with('success', "{$count} content block(s) updated successfully.");
    }

    public function create(): View
    {
        $this->authorize('create', ContentBlock::class);

        return view('contentblocks::content-blocks.create', $this->formData());
    }

    public function store(StoreContentBlockRequest $request): RedirectResponse
    {
        $block = $this->contentBlocks->create($request->validated());
        ActivityLogger::log('content_blocks', 'created', $block);
        app(PublicCacheService::class)->clearAll();

        return redirect()
            ->route('admin.content-blocks.edit', $block)
            ->with('success', 'Content block created successfully.')
            ->with('autoUpdatePreview', $request->boolean('auto_update_preview'));
    }

    public function edit(ContentBlock $contentBlock): View
    {
        $this->authorize('update', $contentBlock);

        return view('contentblocks::content-blocks.edit', [
            'contentBlock' => $contentBlock,
            'initialPreview' => $this->shouldLoadInitialPreview($contentBlock)
                ? app(ContentBlockPreviewService::class)->retrieve(
                    $this->previewAttributesFromModel($contentBlock),
                )
                : null,
            ...$this->formData($contentBlock),
        ]);
    }

    public function preview(PreviewContentBlockRequest $request): JsonResponse
    {
        $existing = $request->route('contentBlock');

        return response()->json(
            app(ContentBlockPreviewService::class)->retrieve(
                $request->validated(),
                max(1, (int) $request->input('preview_page', 1)),
                $existing instanceof ContentBlock ? $existing : null,
            ),
        );
    }

    public function update(UpdateContentBlockRequest $request, ContentBlock $contentBlock): RedirectResponse
    {
        $this->contentBlocks->update($contentBlock, $request->validated());
        ActivityLogger::log('content_blocks', 'updated', $contentBlock);
        app(PublicCacheService::class)->clearAll();

        return redirect()
            ->route('admin.content-blocks.edit', $contentBlock)
            ->with('success', 'Content block updated successfully.')
            ->with('autoUpdatePreview', $request->boolean('auto_update_preview'));
    }

    public function destroy(ContentBlock $contentBlock): RedirectResponse
    {
        $this->authorize('delete', $contentBlock);
        ActivityLogger::log('content_blocks', 'deleted', $contentBlock);
        $this->contentBlocks->delete($contentBlock);
        app(PublicCacheService::class)->clearAll();

        return redirect()->route('admin.content-blocks.index')->with('success', 'Content block deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function formData(?ContentBlock $contentBlock = null): array
    {
        return [
            'contentTypes' => app(ContentTypeRegistry::class)->options(),
            'fieldOptions' => app(ContentBlockFieldRegistry::class)->options(),
            'sortOptions' => app(ContentBlockFieldRegistry::class)->sortableOptions(),
            'formatterOptions' => \App\Modules\ContentBlocks\Enums\ContentBlockFormatter::options(),
            'operatorLabels' => app(ContentBlockFilterOperators::class)->labels(),
            'operatorsByFieldType' => app(ContentBlockFilterOperators::class)->byFieldType(),
            'fieldMeta' => app(ContentBlockFieldRegistry::class)->all(),
            'previewRoute' => $contentBlock
                ? route('admin.content-blocks.preview-existing', $contentBlock)
                : route('admin.content-blocks.preview'),
        ];
    }

    protected function shouldLoadInitialPreview(ContentBlock $contentBlock): bool
    {
        if (session()->has('autoUpdatePreview')) {
            return (bool) session()->pull('autoUpdatePreview');
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    protected function previewAttributesFromModel(ContentBlock $contentBlock): array
    {
        return [
            'key' => $contentBlock->key,
            'name' => $contentBlock->name,
            'icon' => $contentBlock->icon,
            'status' => $contentBlock->status->value,
            'content_types' => $contentBlock->content_types ?? [],
            'fields' => $contentBlock->fields ?? [],
            'filters' => $contentBlock->filters ?? [],
            'sort_field' => $contentBlock->sort_field,
            'sort_direction' => $contentBlock->sort_direction,
            'items_per_page' => $contentBlock->items_per_page,
            'pagination_enabled' => $contentBlock->pagination_enabled,
            'formatter' => $contentBlock->formatter->value,
            'wrapper_class' => $contentBlock->wrapper_class,
            'wrapper_id' => $contentBlock->wrapper_id,
            'settings' => $contentBlock->settings,
        ];
    }
}
