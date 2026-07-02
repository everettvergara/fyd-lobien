<?php

namespace App\Modules\SEO\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SEO\Models\SeoSettings;
use App\Modules\SEO\Requests\UpdateSitemapSettingsRequest;
use App\Modules\SEO\Services\SitemapService;
use App\Services\ActivityLogger;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SitemapSettingsController extends Controller
{
    public function __construct(
        protected SettingsService $settings,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', SeoSettings::class);

        $settings = [
            'sitemap_enabled' => $this->settings->get('seo', 'sitemap_enabled', true),
            'homepage_include' => $this->settings->get('seo', 'homepage_include', true),
            'homepage_changefreq' => $this->settings->get('seo', 'homepage_changefreq', 'weekly'),
            'homepage_priority' => $this->settings->get('seo', 'homepage_priority', '1.0'),
            'default_changefreq_page' => $this->settings->get('seo', 'default_changefreq_page', 'monthly'),
            'default_changefreq_article' => $this->settings->get('seo', 'default_changefreq_article', 'weekly'),
            'default_priority' => $this->settings->get('seo', 'default_priority', '0.5'),
        ];

        return view('seo::sitemap.index', compact('settings'));
    }

    public function update(UpdateSitemapSettingsRequest $request): RedirectResponse
    {
        $this->authorize('update', SeoSettings::class);

        foreach ($request->validated() as $key => $value) {
            $type = in_array($key, ['sitemap_enabled', 'homepage_include'], true) ? 'boolean' : 'string';
            $stored = $type === 'boolean'
                ? (filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false')
                : (string) $value;

            $this->settings->set('seo', $key, $stored, $type);
        }

        SitemapService::forgetCache();
        ActivityLogger::log('seo', 'sitemap_settings_updated');

        return redirect()->route('admin.seo.sitemap.index')->with('success', 'Sitemap settings updated successfully.');
    }
}
