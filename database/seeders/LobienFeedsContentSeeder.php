<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\Media;
use App\Models\User;
use App\Modules\Content\Models\Content;
use App\Modules\Content\Services\ContentPageSyncService;
use App\Modules\Content\Services\ContentTypeSyncService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Imports Lobien content feeds from the old Drupal site export.
 *
 * Source: D:\tmp\lobien_uploads\feeds-from-old-site.json
 * Downloads featured images from https://www.lobiengroup.com
 *
 * @see config/content-types.php
 */
class LobienFeedsContentSeeder extends Seeder
{
    protected const REMOTE_BASE = 'https://www.lobiengroup.com';

    protected const FEED_PATH = 'D:\\tmp\\lobien_uploads\\feeds-from-old-site.json';

    /** @var array<string, string> */
    protected const TYPE_MAP = [
        'Article' => 'article',
        'Videos' => 'videos',
        'Property Tours' => 'property_tours',
        'Social Media' => 'social_media',
        'Downloadable' => 'downloadable',
    ];

    /** @var array<string, int> */
    protected array $mediaCache = [];

    public function run(): void
    {
        $feedPath = $this->resolveFeedPath();

        if ($feedPath === null) {
            $this->command?->error('Feed JSON not found. Expected: '.self::FEED_PATH);

            return;
        }

        app(ContentTypeSyncService::class)->syncFromConfig();

        $admin = User::query()->where('email', 'admin@fyd.local')->first()
            ?? User::query()->orderBy('id')->first();

        if ($admin === null) {
            $this->command?->error('No admin user found. Run AuthenticationSeeder first.');

            return;
        }

        $payload = json_decode((string) file_get_contents($feedPath), true);

        if (! is_array($payload) || ! isset($payload['items']) || ! is_array($payload['items'])) {
            $this->command?->error('Invalid feed JSON structure.');

            return;
        }

        /** @var ContentPageSyncService $pageSync */
        $pageSync = app(ContentPageSyncService::class);

        $counts = [
            'created' => 0,
            'updated' => 0,
            'images' => 0,
            'skipped' => 0,
            'failed_images' => 0,
        ];

        foreach ($payload['items'] as $index => $item) {
            if (! is_array($item)) {
                $counts['skipped']++;

                continue;
            }

            $label = (string) ($item['content_type'] ?? '');
            $contentType = self::TYPE_MAP[$label] ?? null;

            if ($contentType === null) {
                $this->command?->warn("Skipping unknown content type [{$label}] at index {$index}");
                $counts['skipped']++;

                continue;
            }

            $title = trim((string) ($item['title'] ?? ''));

            if ($title === '') {
                $counts['skipped']++;

                continue;
            }

            $slug = $this->slugFromItem($item, $contentType);

            if ($slug === '') {
                $counts['skipped']++;

                continue;
            }

            $existing = Content::withTrashed()->where('slug', $slug)->first();
            $wasExisting = $existing !== null && $existing->deleted_at === null;

            if ($existing !== null && $existing->trashed()) {
                $existing->restore();
            }

            $featuredImageId = $existing?->featured_image_id;
            $image = $item['image'] ?? null;

            if (is_array($image) && ! empty($image['src'])) {
                $mediaId = $this->importRemoteMedia(
                    (string) $image['src'],
                    trim((string) ($image['alt'] ?? '')) ?: $title,
                    $admin->id,
                );

                if ($mediaId !== null) {
                    $featuredImageId = $mediaId;
                    $counts['images']++;
                } else {
                    $counts['failed_images']++;
                }
            }

            $summary = $this->buildSummary($item);
            $body = $this->rewriteBodyHtml((string) ($item['body'] ?? ''));
            $urlLink = $this->resolveUrlLink($item, $body);
            $publishedAt = $this->resolvePublishedAt($item);

            $attributes = [
                'content_type' => $contentType,
                'title' => $title,
                'summary' => $summary,
                'body' => $body,
                'url_link' => $urlLink,
                'status' => ContentStatus::Published,
                'published_at' => $publishedAt,
                'author_id' => $admin->id,
            ];

            if ($featuredImageId !== null) {
                $attributes['featured_image_id'] = $featuredImageId;
            }

            $content = Content::updateOrCreate(
                ['slug' => $slug],
                $attributes,
            );

            $content->saveSeo([
                'seo_title' => $title.' — Lobien Realty Group',
                'meta_description' => Str::limit(strip_tags($summary !== '' ? $summary : $body), 160, ''),
            ]);

            $pageSync->syncContentPage($content->fresh());

            $wasExisting ? $counts['updated']++ : $counts['created']++;

            if (($index + 1) % 25 === 0) {
                $this->command?->info('Processed '.($index + 1).' / '.count($payload['items']).' items…');
            }
        }

        $this->command?->info(sprintf(
            'Lobien feeds import done: %d created, %d updated, %d images, %d failed images, %d skipped.',
            $counts['created'],
            $counts['updated'],
            $counts['images'],
            $counts['failed_images'],
            $counts['skipped'],
        ));
    }

    protected function resolveFeedPath(): ?string
    {
        if (is_file(self::FEED_PATH)) {
            return self::FEED_PATH;
        }

        $fallback = database_path('data/feeds-from-old-site.json');

        return is_file($fallback) ? $fallback : null;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function slugFromItem(array $item, string $contentType): string
    {
        $path = (string) ($item['link_to_content_path'] ?? $item['link_to_content'] ?? '');
        $path = trim($path);

        if ($path !== '' && preg_match('#^/(?:article|articles|videos|property-tours|downloadable|social-media)/(.+)$#', $path, $m)) {
            $slug = Str::slug(urldecode($m[1]));

            if ($slug !== '') {
                return $this->ensureUniqueSlug($slug, $contentType);
            }
        }

        if ($path !== '' && preg_match('#^/node/(\d+)$#', $path, $m)) {
            $titleSlug = Str::slug((string) ($item['title'] ?? ''));

            // Social profiles: stable clean slugs from title (facebook, youtube, …).
            if ($contentType === 'social_media' && $titleSlug !== '') {
                return $this->ensureUniqueSlug($titleSlug, $contentType);
            }

            // Other Drupal node paths: append node id so same-title rows stay distinct & idempotent.
            $slug = $titleSlug !== '' ? $titleSlug.'-'.$m[1] : 'node-'.$m[1];

            return $this->ensureUniqueSlug($slug, $contentType);
        }

        $fallback = Str::slug((string) ($item['title'] ?? ''));

        return $fallback !== '' ? $this->ensureUniqueSlug($fallback, $contentType) : '';
    }

    protected function ensureUniqueSlug(string $slug, string $contentType): string
    {
        $candidate = $slug;
        $suffix = 2;

        while (true) {
            $existing = Content::withTrashed()
                ->where('slug', $candidate)
                ->first();

            if ($existing === null || $existing->content_type === $contentType) {
                return $candidate;
            }

            $candidate = $slug.'-'.$suffix;
            $suffix++;
        }
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function buildSummary(array $item): string
    {
        $summary = trim((string) ($item['body_summary_text'] ?? ''));

        if ($summary === '') {
            $summary = trim(strip_tags((string) ($item['body_summary'] ?? '')));
        }

        $videoType = trim((string) ($item['video_type'] ?? ''));

        if ($videoType !== '') {
            $summary = $summary !== ''
                ? '['.$videoType.'] '.$summary
                : '['.$videoType.']';
        }

        return Str::limit($summary, 500, '');
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function resolveUrlLink(array $item, string $body): ?string
    {
        $social = trim((string) ($item['social_media_link'] ?? ''));

        if ($social !== '' && filter_var($social, FILTER_VALIDATE_URL)) {
            return $social;
        }

        if (preg_match('#https?://(?:www\.)?(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)[^\s"\'<>]+#i', $body, $m)) {
            return html_entity_decode(rtrim($m[0], '.,);'));
        }

        if (preg_match('#https?://(?:www\.)?vimeo\.com/[^\s"\'<>]+#i', $body, $m)) {
            return html_entity_decode(rtrim($m[0], '.,);'));
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function resolvePublishedAt(array $item): Carbon
    {
        $datetime = $item['created']['datetime'] ?? null;

        if (is_string($datetime) && $datetime !== '') {
            try {
                return Carbon::parse($datetime);
            } catch (\Throwable) {
                // fall through
            }
        }

        return now();
    }

    protected function rewriteBodyHtml(string $body): string
    {
        if ($body === '') {
            return '';
        }

        // Point Drupal file / relative site paths at the live origin so images & links keep working.
        $body = preg_replace(
            '#(?<=["\'\s=(])(/sites/default/files/[^"\'\s>]+)#i',
            self::REMOTE_BASE.'$1',
            $body,
        ) ?? $body;

        $body = preg_replace(
            '#(?<=["\'=(])/((?:article|articles|videos|property-tours|downloadable|node)/[^"\'\s>]+)#i',
            self::REMOTE_BASE.'/$1',
            $body,
        ) ?? $body;

        return $body;
    }

    protected function importRemoteMedia(string $relativePath, string $alt, int $adminId): ?int
    {
        $relativePath = '/'.ltrim($relativePath, '/');

        if (isset($this->mediaCache[$relativePath])) {
            return $this->mediaCache[$relativePath];
        }

        $extension = strtolower(pathinfo(parse_url($relativePath, PHP_URL_PATH) ?? $relativePath, PATHINFO_EXTENSION)) ?: 'jpg';
        $storagePath = 'media/feeds/'.md5($relativePath).'.'.$extension;

        $existing = Media::query()->where('path', $storagePath)->first();

        if ($existing !== null) {
            return $this->mediaCache[$relativePath] = $existing->id;
        }

        $url = self::REMOTE_BASE.$relativePath;

        try {
            $response = Http::timeout(45)
                ->withHeaders(['User-Agent' => 'FYD-Lobien-Importer/1.0'])
                ->get($url);

            if (! $response->successful()) {
                $this->command?->warn("Image download failed ({$response->status()}): {$url}");

                return null;
            }

            $contents = $response->body();

            if ($contents === '') {
                return null;
            }

            Storage::disk('public')->put($storagePath, $contents);

            $mimeType = match ($extension) {
                'png' => 'image/png',
                'webp' => 'image/webp',
                'gif' => 'image/gif',
                'jpeg', 'jpg' => 'image/jpeg',
                default => 'image/jpeg',
            };

            $media = Media::create([
                'filename' => basename($storagePath),
                'original_filename' => basename(urldecode($relativePath)),
                'title' => $alt !== '' ? $alt : basename(urldecode($relativePath)),
                'alt_text' => $alt,
                'mime_type' => $mimeType,
                'extension' => $extension,
                'size' => strlen($contents),
                'disk' => 'public',
                'path' => $storagePath,
                'uploaded_by' => $adminId,
            ]);

            return $this->mediaCache[$relativePath] = $media->id;
        } catch (\Throwable $e) {
            $this->command?->warn('Image download error: '.$e->getMessage());

            return null;
        }
    }
}
