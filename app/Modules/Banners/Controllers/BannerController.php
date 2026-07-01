<?php

namespace App\Modules\Banners\Controllers;

use App\Enums\BannerPlacement;
use App\Enums\BannerType;
use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Modules\Banners\Models\Banner;
use App\Modules\Banners\Requests\StoreBannerRequest;
use App\Modules\Banners\Requests\UpdateBannerRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BannerController extends Controller
{
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
            'statuses' => ContentStatus::cases(),
        ]);
    }

    public function store(StoreBannerRequest $request): RedirectResponse
    {
        $banner = Banner::create($request->validated());
        ActivityLogger::log('banners', 'created', $banner);

        return redirect()->route('admin.banners.index')->with('success', 'Banner created successfully.');
    }

    public function edit(Banner $banner): View
    {
        $this->authorize('update', $banner);

        return view('banners::banners.edit', [
            'banner' => $banner,
            'types' => BannerType::cases(),
            'placements' => BannerPlacement::cases(),
            'statuses' => ContentStatus::cases(),
        ]);
    }

    public function update(UpdateBannerRequest $request, Banner $banner): RedirectResponse
    {
        $banner->update($request->validated());
        ActivityLogger::log('banners', 'updated', $banner);

        return redirect()->route('admin.banners.index')->with('success', 'Banner updated successfully.');
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        $this->authorize('delete', $banner);
        ActivityLogger::log('banners', 'deleted', $banner);
        $banner->delete();

        return redirect()->route('admin.banners.index')->with('success', 'Banner deleted successfully.');
    }

    public function publish(Banner $banner): RedirectResponse
    {
        $this->authorize('publish', $banner);
        $banner->update(['status' => ContentStatus::Published, 'published_at' => now()]);
        ActivityLogger::log('banners', 'published', $banner);

        return back()->with('success', 'Banner published successfully.');
    }
}
