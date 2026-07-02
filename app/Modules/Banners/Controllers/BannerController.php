<?php

namespace App\Modules\Banners\Controllers;

use App\Framework\Admin\List\AdminBulkActionService;
use App\Http\Controllers\Controller;
use App\Modules\Banners\Models\Banner;
use App\Modules\Banners\Requests\StoreBannerRequest;
use App\Modules\Banners\Requests\UpdateBannerRequest;
use App\Modules\Banners\Services\BannerAdminListService;
use App\Modules\Banners\Services\BannerRenderingService;
use App\Modules\Banners\Services\BannerService;
use App\Modules\Banners\Services\BannerFormSchemaService;
use App\Modules\Banners\Services\BannerTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BannerController extends Controller
{
    public function __construct(
        protected BannerAdminListService $bannerList,
        protected BannerService $banners,
        protected BannerTemplateService $templates,
        protected BannerRenderingService $rendering,
        protected BannerFormSchemaService $formSchema,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Banner::class);

        return view('banners::banners.index', [
            'list' => $this->bannerList->result($request),
        ]);
    }

    public function bulk(Request $request, AdminBulkActionService $bulkActions): RedirectResponse
    {
        $this->authorize('viewAny', Banner::class);

        $count = $bulkActions->execute($this->bannerList->definition(), $request);

        return back()->with('success', "{$count} banner(s) updated successfully.");
    }

    public function create(): View
    {
        $this->authorize('create', Banner::class);

        return view('banners::banners.create', [
            'form' => $this->formState(),
            'templates' => $this->templates->active(),
            'statuses' => \App\Enums\ContentStatus::cases(),
        ]);
    }

    public function store(StoreBannerRequest $request): RedirectResponse
    {
        $this->banners->create($request->validated());

        return redirect()->route('admin.banners.index')->with('success', 'Banner created successfully.');
    }

    public function edit(Banner $banner): View
    {
        $this->authorize('update', $banner);
        $banner->load(['desktopImage', 'mobileImage', 'backgroundImage', 'template', 'slides.contentBlocks.buttons', 'slides.mediaAssignments.media']);

        return view('banners::banners.edit', [
            'banner' => $banner,
            'form' => $this->formState($banner),
            'templates' => $this->templates->active(),
            'statuses' => \App\Enums\ContentStatus::cases(),
        ]);
    }

    public function update(UpdateBannerRequest $request, Banner $banner): RedirectResponse
    {
        $this->banners->update($banner, $request->validated());

        return redirect()->route('admin.banners.index')->with('success', 'Banner updated successfully.');
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        $this->authorize('delete', $banner);
        $this->banners->delete($banner);

        return redirect()->route('admin.banners.index')->with('success', 'Banner deleted successfully.');
    }

    public function publish(Banner $banner): RedirectResponse
    {
        $this->authorize('publish', $banner);
        $this->banners->publish($banner);

        return back()->with('success', 'Banner published successfully.');
    }

    public function unpublish(Banner $banner): RedirectResponse
    {
        $this->authorize('publish', $banner);
        $this->banners->unpublish($banner);

        return back()->with('success', 'Banner unpublished successfully.');
    }

    public function archive(Banner $banner): RedirectResponse
    {
        $this->authorize('archive', $banner);
        $this->banners->archive($banner);

        return back()->with('success', 'Banner archived successfully.');
    }

    public function duplicate(Banner $banner): RedirectResponse
    {
        $this->authorize('create', Banner::class);

        $duplicate = $this->banners->duplicate($banner);

        return redirect()->route('admin.banners.edit', $duplicate)->with('success', 'Banner duplicated.');
    }

    public function preview(Banner $banner): View
    {
        $this->authorize('view', $banner);

        return view('banners::banners.preview', [
            'banner' => $banner,
            'payload' => $this->rendering->dto($banner),
        ]);
    }

    protected function formState(?Banner $banner = null): array
    {
        $templates = $this->templates->active();
        $templateId = (int) old('template_id', request()->integer('template_id') ?: ($banner?->template_id ?? $templates->first()?->id));
        $template = $templates->firstWhere('id', $templateId);
        $schema = $this->formSchema->resolve($template);

        if ($banner && (int) $banner->template_id !== $templateId && ! old('slides')) {
            $slides = $this->formSchema->emptySlides($schema);
        } else {
            $slides = old('slides', $this->formSchema->slidesFromBanner($banner, $schema));
        }

        $slides = $this->formSchema->alignSlidesToSchema($slides, $schema);
        $settings = $banner?->settings ?? [];
        $effects = $banner?->effect_settings ?? [];

        return [
            'name' => $banner?->name,
            'key' => $banner?->key,
            'template_id' => $templateId,
            'template_key' => $template?->key,
            'slides' => $slides,
            'template_schemas' => $this->formSchema->schemasForTemplates($templates),
            'active_schema' => $schema,
            'status' => $banner?->status?->value ?? 'draft',
            'sort_order' => $banner?->sort_order ?? 0,
            'column_ratio' => $settings['column_ratio'] ?? '50/50',
            'effect' => $effects['effect'] ?? 'none',
            'animation_speed' => $effects['speed'] ?? 500,
            'delay' => $effects['delay'] ?? 0,
            'loop' => $effects['loop'] ?? false,
            'autoplay' => $effects['autoplay'] ?? false,
        ];
    }
}
