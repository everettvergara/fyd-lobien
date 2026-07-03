<?php

namespace App\Modules\PageManager\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PageManager\Models\PageMaster;
use App\Modules\PageManager\Requests\UpdatePageMasterRequest;
use App\Modules\PageManager\Services\PageBlockSyncService;
use App\Modules\Cache\Services\PublicCacheService;
use App\Modules\SEO\Services\SitemapService;
use App\Services\ActivityLogger;
use App\Services\Public\PublicBlockRegistry;
use App\Services\Theme\ThemeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PageMasterController extends Controller
{
    public function __construct(
        protected PageBlockSyncService $blockSync,
        protected ThemeService $theme,
        protected PublicBlockRegistry $blockRegistry,
    ) {}

    public function edit(): View
    {
        $pageMaster = PageMaster::instance();
        $this->authorize('update', $pageMaster);
        $pageMaster->load('blocks');

        return view('pagemanager::page-master.edit', [
            'pageMaster' => $pageMaster,
            'regions' => $this->theme->activeRegions(),
            'blockPalette' => $this->blockRegistry->paletteForAdmin(),
        ]);
    }

    public function update(UpdatePageMasterRequest $request): RedirectResponse
    {
        $pageMaster = PageMaster::instance();
        $this->authorize('update', $pageMaster);

        $pageMaster->update([
            ...$request->safe()->except(['blocks', ...array_keys(\App\Support\SeoFields::rules())]),
            'is_configured' => true,
        ]);

        $this->blockSync->syncMasterBlocks($pageMaster, $request->input('blocks', []));
        SitemapService::forgetCache();
        app(PublicCacheService::class)->clearAll();
        ActivityLogger::log('pages', 'page_master_updated', $pageMaster);

        return redirect()->route('admin.page-master.edit')->with('success', 'Page Master updated successfully.');
    }
}
