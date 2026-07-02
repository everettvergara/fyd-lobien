<?php

namespace App\Modules\SEO\Services;

use App\Enums\SitemapChangeFrequency;
use App\Modules\Content\Models\Content;
use App\Services\SettingsService;
use Illuminate\Support\Facades\Cache;

class SitemapService
{
    public const CACHE_KEY = 'seo.sitemap.xml';

    protected const RESERVED_SLUGS = ['blog', 'search', 'admin', 'api'];

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

        if ($this->settings->get('seo', 'homepage_include', true)) {
            $urls[] = $this->homepageEntry();
        }

        Content::published()
            ->with('seoMeta')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->each(function (Content $content) use (&$urls) {
                if ($entry = $this->contentEntry($content)) {
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
    protected function contentEntry(Content $content): ?array
    {
        if (in_array($content->slug, self::RESERVED_SLUGS, true)) {
            return null;
        }

        $seo = $content->seoMeta;

        if ($seo && $seo->sitemap_include === false) {
            return null;
        }

        $robots = $seo?->robots ?? 'index,follow';
        if (str_contains(strtolower($robots), 'noindex')) {
            return null;
        }

        $loc = $seo?->canonical_url ?: url($content->slug);

        return [
            'loc' => $loc,
            'lastmod' => $content->updated_at->toAtomString(),
            'changefreq' => $this->changefreqForContent($content),
            'priority' => $this->formatPriority(
                $seo?->sitemap_priority ?? $this->settings->get('seo', 'default_priority', '0.5')
            ),
        ];
    }

    /**
     * @return array{loc: string, lastmod: string, changefreq: string, priority: string}
     */
    protected function homepageEntry(): array
    {
        return [
            'loc' => url('/'),
            'lastmod' => now()->toAtomString(),
            'changefreq' => (string) $this->settings->get('seo', 'homepage_changefreq', 'weekly'),
            'priority' => $this->formatPriority($this->settings->get('seo', 'homepage_priority', '1.0')),
        ];
    }

    protected function changefreqForContent(Content $content): string
    {
        $override = $content->seoMeta?->sitemap_changefreq;
        if ($override) {
            return $override;
        }

        $key = $content->content_type === 'article'
            ? 'default_changefreq_article'
            : 'default_changefreq_page';

        return (string) $this->settings->get('seo', $key, 'monthly');
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
