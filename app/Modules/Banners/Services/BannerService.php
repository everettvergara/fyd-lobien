<?php

namespace App\Modules\Banners\Services;

use App\Modules\Banners\Models\Banner;
use App\Services\ActivityLogger;

class BannerService
{
    public function create(array $validated): Banner
    {
        $banner = Banner::create($validated);
        ActivityLogger::log('banners', 'created', $banner);

        return $banner;
    }

    public function update(Banner $banner, array $validated): Banner
    {
        $banner->update($validated);
        ActivityLogger::log('banners', 'updated', $banner);

        return $banner;
    }

    public function delete(Banner $banner): void
    {
        ActivityLogger::log('banners', 'deleted', $banner);
        $banner->delete();
    }
}
