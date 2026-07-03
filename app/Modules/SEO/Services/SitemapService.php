<?php

namespace App\Modules\SEO\Services;

use App\Enums\ContentStatus;
use App\Enums\SitemapChangeFrequency;
use App\Modules\PageManager\Models\Page;
use App\Services\SettingsService;
use Illuminate\Support\Facades\Cache;

class SitemapService
{
    public const CACHE_KEY = 'seo.sitemap.xml';

    public function __construct(
        protected SettingsService $settings,
    ) {}

    public function isEnabled(): bool
    {
        return (bool) $this->settings->get('seo', 'sitemap_enabled', true);
    }

    public function render(): ?string
    {
        if (! $this->isEnabled()) {
            return null;
        }

        return Cache::remember(self::CACHE_KEY, 3600, fn () => $this->buildXml());
    }

    public static function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    protected function buildXml(): string
    {
        $urls = [];

        Page::query()
            ->with('seoMeta')
            ->where('status', ContentStatus::Published)
            ->orderBy('updated_at', 'desc')
            ->get()
            ->each(function (Page $page) use (&$urls) {
                if ($entry = $this->pageEntry($page)) {
                    $urls[] = $entry;
                }
            });

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.htmlspecialchars($url['loc'], ENT_XML1)."</loc>\n";
            $xml .= '    <lastmod>'.htmlspecialchars($url['lastmod'], ENT_XML1)."</lastmod>\n";
            $xml .= '    <changefreq>'.htmlspecialchars($url['changefreq'], ENT_XML1)."</changefreq>\n";
            $xml .= '    <priority>'.htmlspecialchars($url['priority'], ENT_XML1)."</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }

    /**
     * @return array{loc: string, lastmod: string, changefreq: string, priority: string}|null
     */
    protected function pageEntry(Page $page): ?array
    {
        $seo = $page->seoMeta;

        if ($seo && $seo->sitemap_include === false) {
            return null;
        }

        $robots = $seo?->robots ?? 'index,follow';
        if (str_contains(strtolower($robots), 'noindex')) {
            return null;
        }

        $loc = $seo?->canonical_url ?: url($page->path === '/' ? '/' : ltrim($page->path, '/'));

        $priority = $page->path === '/'
            ? $this->settings->get('seo', 'homepage_priority', '1.0')
            : ($seo?->sitemap_priority ?? $this->settings->get('seo', 'default_priority', '0.5'));

        $changefreq = $page->path === '/'
            ? (string) $this->settings->get('seo', 'homepage_changefreq', 'weekly')
            : ($seo?->sitemap_changefreq ?? (string) $this->settings->get('seo', 'default_changefreq_page', 'monthly'));

        return [
            'loc' => $loc,
            'lastmod' => $page->updated_at->toAtomString(),
            'changefreq' => $changefreq,
            'priority' => $this->formatPriority($priority),
        ];
    }

    protected function formatPriority(mixed $priority): string
    {
        return number_format((float) $priority, 1, '.', '');
    }

    public function defaultChangefreqLabel(?string $changefreq): string
    {
        if (! $changefreq) {
            return 'Default';
        }

        return SitemapChangeFrequency::tryFrom($changefreq)?->label() ?? $changefreq;
    }
}
