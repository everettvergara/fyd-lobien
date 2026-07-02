<?php

namespace App\Modules\Banners\Controllers;

use App\Enums\BannerPlacement;
use App\Enums\BannerType;
use App\Http\Controllers\Controller;
use App\Modules\Banners\Models\Banner;
use App\Modules\Banners\Requests\StoreBannerRequest;
use App\Modules\Banners\Requests\UpdateBannerRequest;
use App\Modules\Banners\Services\BannerService;
use App\Services\PublishingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BannerController extends Controller
{
    public function __construct(
        protected BannerService $banners,
        protected PublishingService $publishing,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', Banner::class);
        $banners = Banner::latest()->paginate(15);

        return view('banners::banners.index', compact('banners'));
    }

    public function create(): View
    {
        $this->authorize('create', Banner::class);

        return view('banners::banners.create', [
            'types' => BannerType::cases(),
            'placements' => BannerPlacement::cases(),
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
        $banner->load(['desktopImage', 'mobileImage', 'backgroundImage']);

        return view('banners::banners.edit', [
            'banner' => $banner,
            'types' => BannerType::cases(),
            'placements' => BannerPlacement::cases(),
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
        $this->publishing->publish($banner, 'banners');

        return back()->with('success', 'Banner published successfully.');
    }

    public function duplicate(Banner $banner): RedirectResponse
    {
        $this->authorize('create', Banner::class);

        $duplicate = $this->publishing->duplicate($banner, 'banners', [
            'name' => $banner->name.' (Copy)',
        ]);

        return redirect()->route('admin.banners.edit', $duplicate)->with('success', 'Banner duplicated.');
    }
}
