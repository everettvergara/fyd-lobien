<?php

namespace Database\Seeders;

use App\Enums\BannerType;
use App\Enums\ContentStatus;
use App\Models\Media;
use App\Models\User;
use App\Modules\Banners\Models\Banner;
use App\Modules\Banners\Models\BannerTemplate;
use App\Modules\Banners\Services\BannerService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Seeds Careers page banners from https://www.lobiengroup.com/index.php/careers
 *
 * @see contrib_themes/lobien/README.md
 */
class LobienCareersBannersSeeder extends Seeder
{
    private const REMOTE_BASE = 'https://www.lobiengroup.com/sites/default/files/';

    /** @var array<string, int> */
    private array $mediaCache = [];

    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@fyd.local')->first();

        if ($admin === null) {
            return;
        }

        $this->seedAllBanners($admin->id);
    }

    /**
     * Banner keys attached to the /careers page (hero first, then main stack).
     *
     * @return list<string>
     */
    public static function pageBannerKeys(): array
    {
        return [
            'careers-header',
        ];
    }

    protected function seedAllBanners(int $adminId): void
    {
        $bannerService = app(BannerService::class);
        $templates = BannerTemplate::query()
            ->whereIn('key', ['inner_page'])
            ->get()
            ->keyBy('key');

        $sortOrder = 0;

        foreach ($this->bannerDefinitions($adminId) as $definition) {
            $template = $templates->get($definition['template_key']);

            if ($template === null) {
                continue;
            }

            $banner = Banner::updateOrCreate(
                ['key' => $definition['key']],
                [
                    'name' => $definition['name'],
                    'title' => $definition['title'] ?? $definition['name'],
                    'subtitle' => $definition['subtitle'] ?? null,
                    'description' => $definition['description'] ?? null,
                    'type' => BannerType::Hero,
                    'template_id' => $template->id,
                    'sort_order' => $sortOrder++,
                    'effect_settings' => [
                        'effect' => 'none',
                        'speed' => 500,
                        'delay' => 0,
                        'loop' => false,
                        'autoplay' => false,
                    ],
                    'status' => ContentStatus::Published,
                ]
            );

            $bannerService->syncStructure($banner, array_merge(
                ['template_id' => $template->id],
                $definition['structure'],
            ));
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function bannerDefinitions(int $adminId): array
    {
        $headerImage = $this->importRemoteMedia(
            'banner/Stock%20photos%20for%20Website%20%286000%20x%202595%20px%29.jpg',
            'Lobien Realty Group careers',
            $adminId,
        );

        return [
            [
                'key' => 'careers-header',
                'name' => 'Careers — Page Header',
                'template_key' => 'inner_page',
                'structure' => [
                    'slides' => [[
                        'name' => 'Default',
                        'blocks' => [[
                            'region' => 'main',
                            'type' => 'content',
                            'headline' => 'BE PART OF OUR GROWING TEAM',
                            'subheading' => 'Lobien Realty Group',
                            'description' => '',
                            'buttons' => [],
                        ]],
                        'media' => $this->mediaSlot('background_image', $headerImage),
                    ]],
                ],
            ],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    protected function mediaSlot(string $slot, ?int $mediaId, ?string $alt = null): array
    {
        if ($mediaId === null) {
            return [];
        }

        return [
            $slot => [
                'media_id' => $mediaId,
                'alt_text' => $alt,
            ],
        ];
    }

    protected function importRemoteMedia(string $relativePath, string $alt, int $adminId): ?int
    {
        if (isset($this->mediaCache[$relativePath])) {
            return $this->mediaCache[$relativePath];
        }

        $extension = strtolower(pathinfo(parse_url($relativePath, PHP_URL_PATH) ?? $relativePath, PATHINFO_EXTENSION)) ?: 'jpg';
        $storagePath = 'media/careers/'.md5($relativePath).'.'.$extension;

        $existing = Media::query()->where('path', $storagePath)->first();

        if ($existing !== null) {
            return $this->mediaCache[$relativePath] = $existing->id;
        }

        $url = self::REMOTE_BASE.$relativePath;

        try {
            $response = Http::timeout(30)->get($url);

            if (! $response->successful()) {
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
                default => 'image/jpeg',
            };

            $media = Media::create([
                'filename' => basename($storagePath),
                'original_filename' => basename(urldecode($relativePath)),
                'title' => $alt,
                'alt_text' => $alt,
                'mime_type' => $mimeType,
                'extension' => $extension,
                'size' => strlen($contents),
                'disk' => 'public',
                'path' => $storagePath,
                'uploaded_by' => $adminId,
            ]);

            return $this->mediaCache[$relativePath] = $media->id;
        } catch (\Throwable) {
            return null;
        }
    }
}
