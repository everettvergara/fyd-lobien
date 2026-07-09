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
 * Seeds Services page banners from https://www.lobiengroup.com/index.php/services
 *
 * @see contrib_themes/lobien/README.md
 */
class LobienServicesBannersSeeder extends Seeder
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
     * Banner keys attached to the /services page (hero first, then main stack).
     *
     * @return list<string>
     */
    public static function pageBannerKeys(): array
    {
        return [
            'services-header',
            'services-intro',
            'services-project-leasing',
            'services-tenant-solutions',
            'services-property-sale-acquisition',
        ];
    }

    protected function seedAllBanners(int $adminId): void
    {
        $bannerService = app(BannerService::class);
        $templates = BannerTemplate::query()
            ->whereIn('key', ['inner_page', 'image_right', 'image_left'])
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
        $introImage = $this->importRemoteMedia(
            'services/Copy%20of%20Team%20Banner%20%28May%202026%29.jpg',
            'Lobien Realty Group services',
            $adminId,
        );
        $projectLeasingImage = $this->importRemoteMedia(
            'services/services2.jpg',
            'Project Leasing',
            $adminId,
        );
        $tenantSolutionsImage = $this->importRemoteMedia(
            'services/services3.jpg',
            'Tenant Solutions',
            $adminId,
        );
        $propertySaleImage = $this->importRemoteMedia(
            'services/Stock%20photos%20for%20Website.jpg',
            'Property Sale and Acquisition',
            $adminId,
        );

        return [
            [
                'key' => 'services-header',
                'name' => 'Services — Page Header',
                'template_key' => 'inner_page',
                'structure' => [
                    'slides' => [[
                        'name' => 'Default',
                        'blocks' => [[
                            'region' => 'main',
                            'type' => 'content',
                            'headline' => 'Services',
                            'subheading' => 'Lobien Realty Group',
                            'description' => 'Project Leasing, Tenant Solutions, and Property Sale and Acquisition from Lobien Realty Group.',
                            'buttons' => [],
                        ]],
                        'media' => $this->mediaSlot('background_image', $introImage),
                    ]],
                ],
            ],
            [
                'key' => 'services-intro',
                'name' => 'Services — Our Services Intro',
                'template_key' => 'image_right',
                'structure' => [
                    'slides' => [[
                        'name' => 'Default',
                        'blocks' => [[
                            'region' => 'main',
                            'type' => 'content',
                            'headline' => 'OUR SERVICES',
                            'description' => 'Lobien Realty Group, Inc. (LRG) is a real estate consultancy firm with three core services: Project Leasing, Tenant Solutions, Property Sale and Acquisition. Learn more about our services below.',
                            'buttons' => [],
                        ]],
                        'media' => $this->mediaSlot('desktop_image', $introImage, 'Lobien Realty Group services'),
                    ]],
                ],
            ],
            [
                'key' => 'services-project-leasing',
                'name' => 'Services — Project Leasing',
                'template_key' => 'image_right',
                'structure' => [
                    'slides' => [[
                        'name' => 'Default',
                        'blocks' => [[
                            'region' => 'main',
                            'type' => 'content',
                            'headline' => 'PROJECT LEASING',
                            'description' => "Benefit from our up-to-date listing of other buildings which are also for lease in the market for an accurate competitive scan for your project and access our vast client/tenant base across industries that will be best suited to your property resulting in your desired tenant mix. Rest assured that the final lease contract will consider both parties' best interest for a long-term landlord-tenant relationship.\n\nOur combined past experience of handling more than a hundred office and commercial buildings in major business districts of the Philippines under exclusive project leasing arrangements has given our core team an unmatched experience and valuable insights in undertaking such a complex but highly rewarding investment activity for our landlords. Rest assured that your LRG team will be with you every step of the way—from project conceptualization, during construction and until the last square meter of your building is leased out.",
                            'buttons' => [],
                        ]],
                        'media' => $this->mediaSlot('desktop_image', $projectLeasingImage, 'Project Leasing'),
                    ]],
                ],
            ],
            [
                'key' => 'services-tenant-solutions',
                'name' => 'Services — Tenant Solutions',
                'template_key' => 'image_left',
                'structure' => [
                    'slides' => [[
                        'name' => 'Default',
                        'blocks' => [[
                            'region' => 'main',
                            'type' => 'content',
                            'headline' => 'TENANT SOLUTIONS',
                            'description' => "Experience our client-focused and end-to-end tenant representation service aimed at looking for the most suitable office space for our clients. Our decade's worth of experience in handling both sides (landlord and tenant) has given us the best perspective in knowing what levers to tweak to arrive at a successful and mutually-beneficial deal.\n\nWe subscribe to the knowledge that the physical office space is not only a good reflection of our client's company culture, pride and values, but it is foremost a main driver of productivity, achievement of company goals and business success. Hence, we strive to fulfill our tenant's real estate requirements and objectives by understanding their specific needs—from cost targets, business and local taxes, expansion plans, transport accessibility, employee security, local labor supply, and many other tenant considerations—to find the most appropriate building and location for their business activity.",
                            'buttons' => [],
                        ]],
                        'media' => $this->mediaSlot('desktop_image', $tenantSolutionsImage, 'Tenant Solutions'),
                    ]],
                ],
            ],
            [
                'key' => 'services-property-sale-acquisition',
                'name' => 'Services — Property Sale and Acquisition',
                'template_key' => 'image_right',
                'structure' => [
                    'slides' => [[
                        'name' => 'Default',
                        'blocks' => [[
                            'region' => 'main',
                            'type' => 'content',
                            'headline' => 'PROPERTY SALE AND ACQUISITION',
                            'rich_text' => $this->propertySaleRichText(),
                            'buttons' => [],
                        ]],
                        'media' => $this->mediaSlot('desktop_image', $propertySaleImage, 'Property Sale and Acquisition'),
                    ]],
                ],
            ],
        ];
    }

    protected function propertySaleRichText(): string
    {
        return implode('', [
            '<p>Our own LRG team, a ready pool of external associates and a team composed of lawyers, bankers, career professionals and other experienced real estate specialists will make sure that your engagement in real estate sale and acquisition will be a very successful and economically rewarding activity.</p>',
            '<p>We continuously work towards having access to a wide listing of properties, with initial due diligence and economic analysis done by our experts, that will merit serious discussions with prospective investors, which we aim to lead to a successful real estate transaction.</p>',
            '<p>Whether it will be a straight sale transaction or an investment that shall require another form of investment structure, we will strive to provide you the most effective strategy and a very transparent advisory services for your real estate venture.</p>',
        ]);
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
        $storagePath = 'media/services/'.md5($relativePath).'.'.$extension;

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
