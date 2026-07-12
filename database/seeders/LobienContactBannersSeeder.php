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
 * Seeds Contact page banners from https://www.lobiengroup.com/index.php/contact-us
 *
 * @see contrib_themes/lobien/README.md
 */
class LobienContactBannersSeeder extends Seeder
{
    private const REMOTE_BASE = 'https://www.lobiengroup.com/sites/default/files/';

    private const ADDRESS = '23F High Street South Corporate Plaza Tower 1, 26th Street Corner 9th Avenue, Bonifacio Global City Taguig City, Philippines 1630';

    private const PHONE_PRIMARY = '+63 999 227 7125';

    private const PHONE_DIRECT = '+632 8983 9311';

    private const EMAIL = 'inquiry@lobiengroup.com';

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
     * Banner keys attached to the /contact page (hero first).
     *
     * @return list<string>
     */
    public static function pageBannerKeys(): array
    {
        return [
            'contact-header',
            'contact-intro',
        ];
    }

    protected function seedAllBanners(int $adminId): void
    {
        $bannerService = app(BannerService::class);
        $templates = BannerTemplate::query()
            ->whereIn('key', ['inner_page', 'image_right'])
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
        $teamImage = $this->importRemoteMedia(
            'images/Copy%20of%20Team%20Banner%20%28May%202026%29_0.jpg',
            'Lobien Realty Group team',
            $adminId,
        );

        $phonePrimaryHref = preg_replace('/\s+/', '', self::PHONE_PRIMARY);
        $phoneDirectHref = preg_replace('/\s+/', '', self::PHONE_DIRECT);

        $richText = '<p>'.e(self::ADDRESS).'</p>'
            .'<ul>'
            .'<li><a href="tel:'.$phonePrimaryHref.'">Call or Message us at '.e(self::PHONE_PRIMARY).'</a></li>'
            .'<li><a href="tel:'.$phoneDirectHref.'">Direct Call at '.e(self::PHONE_DIRECT).'</a></li>'
            .'</ul>'
            .'<p><a href="mailto:'.e(self::EMAIL).'">'.e(self::EMAIL).'</a></p>';

        return [
            [
                'key' => 'contact-header',
                'name' => 'Contact Us — Page Header',
                'template_key' => 'inner_page',
                'structure' => [
                    'slides' => [[
                        'name' => 'Default',
                        'blocks' => [[
                            'region' => 'main',
                            'type' => 'content',
                            'headline' => 'Contact Us',
                            'subheading' => 'Lobien Realty Group',
                            'description' => 'Get in touch with Lobien Realty Group. We\'d love to hear from you.',
                            'buttons' => [],
                        ]],
                        'media' => $this->mediaSlot('background_image', $teamImage),
                    ]],
                ],
            ],
            [
                'key' => 'contact-intro',
                'name' => 'Contact Us — Intro',
                'template_key' => 'image_right',
                'title' => 'CONTACT US',
                'description' => "Got questions or need assistance? We'd love to hear from you! Contact us, and our team will get back to you as soon as possible. Whether it's about our services, support, or anything else, we're here to help!",
                'structure' => [
                    'slides' => [[
                        'name' => 'Default',
                        'blocks' => [[
                            'region' => 'main',
                            'type' => 'content',
                            'headline' => 'CONTACT US',
                            'description' => "Got questions or need assistance? We'd love to hear from you! Contact us, and our team will get back to you as soon as possible. Whether it's about our services, support, or anything else, we're here to help!",
                            'rich_text' => $richText,
                            'buttons' => [],
                        ]],
                        'media' => $this->mediaSlot('desktop_image', $teamImage, 'Lobien Realty Group team'),
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
        $storagePath = 'media/contact/'.md5($relativePath).'.'.$extension;

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
