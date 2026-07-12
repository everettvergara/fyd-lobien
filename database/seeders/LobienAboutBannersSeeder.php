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
 * Seeds About page banners from https://www.lobiengroup.com/index.php/about-us
 *
 * @see contrib_themes/lobien/README.md
 */
class LobienAboutBannersSeeder extends Seeder
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
     * Banner keys attached to the /about page (hero first, then main stack).
     *
     * @return list<string>
     */
    public static function pageBannerKeys(): array
    {
        return [
            'about-header',
            'about-who-we-are',
            'about-history',
            'about-vision',
            'about-mission',
            'about-pillars-row-1',
            'about-pillars-row-2',
            'about-partners-heading',
            'about-partners-row-1',
            'about-partners-row-2',
            'about-people-heading',
            'about-person-sheila',
            'about-person-alex',
            'about-person-steph',
            'about-footer-banner',
        ];
    }

    protected function seedAllBanners(int $adminId): void
    {
        $bannerService = app(BannerService::class);
        $templates = BannerTemplate::query()
            ->whereIn('key', [
                'inner_page',
                'image_right',
                'two_column_full_width',
                'minimal',
                'three_column_full_width',
                'image_left',
                'split_layout',
            ])
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
        $teamIntroImage = $this->importRemoteMedia(
            'images/Copy%20of%20Team%20Banner%20%28May%202026%29.jpg',
            'Lobien Realty Group team',
            $adminId,
        );
        $footerImage = $this->importRemoteMedia(
            'image%20display/Team%20Banner%20%28May%202026%29_0.jpg',
            'Lobien Realty Group team banner',
            $adminId,
        );

        $pillarImages = [
            $this->importRemoteMedia('images/for-investors.png', 'For Investors', $adminId),
            $this->importRemoteMedia('images/for-clients.png', 'For Clients', $adminId),
            $this->importRemoteMedia('images/for-empleyees.png', 'For Employees', $adminId),
            $this->importRemoteMedia('images/for-the-public.png', 'For The Public', $adminId),
        ];

        $partnerImages = [
            $this->importRemoteMedia('our%20partners/0001_2019%20ECCP%20Logo%20Full%20Colour.png', 'ECCP', $adminId),
            $this->importRemoteMedia('our%20partners/0002_MAP-Logo-2025.png', 'MAP', $adminId),
            $this->importRemoteMedia('our%20partners/0003_IBPAP%20Logo.png', 'IBPAP', $adminId),
            $this->importRemoteMedia('our%20partners/0004_Inner%20Wheel.png', 'Inner Wheel', $adminId),
            $this->importRemoteMedia('our%20partners/0005_womenbizph-footer-logo.png', 'WomenBizPH', $adminId),
            $this->importRemoteMedia('our%20partners/0006_FIL-CHI%20BPW.png', 'FIL-CHI BPW', $adminId),
        ];

        $sheilaImage = $this->importRemoteMedia(
            'our%20people/Copy%20of%20Team%20Banner%20%28May%202026%29%20%28800%20x%20800%20px%29%20%281%29.png',
            'Sheila Lobien',
            $adminId,
        );
        $alexImage = $this->importRemoteMedia(
            'our%20people/Untitled%20design.png',
            'Alex Regala',
            $adminId,
        );
        $stephImage = $this->importRemoteMedia(
            'our%20people/Copy%20of%20Team%20Banner%20%28May%202026%29%20%28800%20x%20800%20px%29%20%282%29.png',
            'Steph Ng',
            $adminId,
        );

        return [
            [
                'key' => 'about-header',
                'name' => 'About Us — Page Header',
                'template_key' => 'inner_page',
                'structure' => [
                    'slides' => [[
                        'name' => 'Default',
                        'blocks' => [[
                            'region' => 'main',
                            'type' => 'content',
                            'headline' => 'About Us',
                            'subheading' => 'Lobien Realty Group',
                            'description' => 'Learn about Lobien Realty Group — our history, vision, mission, and leadership team.',
                            'buttons' => [],
                        ]],
                        'media' => $this->mediaSlot('background_image', $footerImage),
                    ]],
                ],
            ],
            [
                'key' => 'about-who-we-are',
                'name' => 'About Us — Who We Are',
                'template_key' => 'image_right',
                'structure' => [
                    'slides' => [[
                        'name' => 'Default',
                        'blocks' => [[
                            'region' => 'main',
                            'type' => 'content',
                            'headline' => 'WHO WE ARE',
                            'description' => "Lobien Realty Group, Inc. (LRG) is a full-service real estate consultancy and property investments strategy firm specializing in office and commercial space leasing, capital investments optimization, and property acquisition and sales.\n\nLRG's core team of property consultants has extensive experience in leasing commercial buildings in key central business districts across the country under exclusive project leasing arrangements and possess deep expertise in managing local and international clients from various industries, businesses, and disciplines.",
                            'buttons' => [],
                        ]],
                        'media' => $this->mediaSlot('desktop_image', $teamIntroImage, 'Lobien Realty Group team'),
                    ]],
                ],
            ],
            [
                'key' => 'about-history',
                'name' => 'About Us — Our History',
                'template_key' => 'two_column_full_width',
                'structure' => [
                    'slides' => [[
                        'name' => 'Default',
                        'blocks' => [
                            [
                                'region' => 'column_1',
                                'type' => 'content',
                                'headline' => 'OUR HISTORY',
                                'description' => "Project Leasing in the commercial real estate sector is an industry-segment strategy used by real estate consultancy firms. It focuses on partnering with investors and landlords to help them find the best tenant mix for their office and commercial property developments.\n\nOur founding partners gained more than a decade's worth of experience working together under the project leasing group of a Fortune 500 real estate consulting firm, having successfully handled more than 100 exclusive arrangements/project leasing projects for Prime and Grade-A commercial buildings across the country's major business districts.",
                                'buttons' => [],
                            ],
                            [
                                'region' => 'column_2',
                                'type' => 'content',
                                'description' => "Together, they have leased out more than two million square meters of office and commercial space to high quality tenants and delivered multi-billion worth of real estate revenues to landlords and investors.\n\nThe founding partners, equipped with invaluable expertise, established their own consultancy firm, Lobien Realty Group, Inc., and boldly entered the Philippine real estate market in 2019.",
                                'buttons' => [],
                            ],
                        ],
                        'media' => [],
                    ]],
                ],
            ],
            [
                'key' => 'about-vision',
                'name' => 'About Us — Vision',
                'template_key' => 'minimal',
                'structure' => [
                    'slides' => [[
                        'name' => 'Default',
                        'blocks' => [[
                            'region' => 'main',
                            'type' => 'content',
                            'headline' => 'Vision',
                            'subheading' => 'Our',
                            'description' => '"Building every partner\'s goal with our world-class real estate services."',
                            'buttons' => [],
                        ]],
                        'media' => [],
                    ]],
                ],
            ],
            [
                'key' => 'about-mission',
                'name' => 'About Us — Mission',
                'template_key' => 'two_column_full_width',
                'structure' => [
                    'slides' => [[
                        'name' => 'Default',
                        'blocks' => [
                            [
                                'region' => 'column_1',
                                'type' => 'content',
                                'headline' => 'OUR MISSION',
                                'description' => 'As real estate services providers, we build lasting partnerships and achieve mutually beneficial goals.',
                                'buttons' => [],
                            ],
                            [
                                'region' => 'column_2',
                                'type' => 'content',
                                'description' => 'We surpass the expectations of our clients and partner with investors to grow their real estate portfolio. Our employees enjoy a high-performance culture anchored on continuous learning, and we foster nation-building through ethical and socially-responsible business strategies.',
                                'buttons' => [],
                            ],
                        ],
                        'media' => [],
                    ]],
                ],
            ],
            [
                'key' => 'about-pillars-row-1',
                'name' => 'About Us — Mission Pillars (1–3)',
                'template_key' => 'three_column_full_width',
                'structure' => [
                    'slides' => [[
                        'name' => 'Default',
                        'blocks' => [
                            [
                                'region' => 'column_1',
                                'type' => 'content',
                                'headline' => 'FOR INVESTORS',
                                'description' => 'We are your partner in diversifying in assets and achieving sustainable growth through investing in real estate services and development.',
                                'buttons' => [],
                            ],
                            [
                                'region' => 'column_2',
                                'type' => 'content',
                                'headline' => 'FOR CLIENTS',
                                'description' => 'We are a reliable group of professionals who provide solutions for the growth of our clients.',
                                'buttons' => [],
                            ],
                            [
                                'region' => 'column_3',
                                'type' => 'content',
                                'headline' => 'FOR EMPLOYEES',
                                'description' => 'Equipping employees in the real estate industry through exposure in a continuous learning environment in order to attain personal aspirations and organizational growth.',
                                'buttons' => [],
                            ],
                        ],
                        'media' => array_merge(
                            $this->mediaSlot('column_1_image', $pillarImages[0], 'For Investors'),
                            $this->mediaSlot('column_2_image', $pillarImages[1], 'For Clients'),
                            $this->mediaSlot('column_3_image', $pillarImages[2], 'For Employees'),
                        ),
                    ]],
                ],
            ],
            [
                'key' => 'about-pillars-row-2',
                'name' => 'About Us — Mission Pillars (4)',
                'template_key' => 'two_column_full_width',
                'structure' => [
                    'slides' => [[
                        'name' => 'Default',
                        'blocks' => [
                            [
                                'region' => 'column_1',
                                'type' => 'content',
                                'headline' => 'FOR THE PUBLIC',
                                'description' => 'We foster nation-building through ethical and socially responsible strategies.',
                                'buttons' => [],
                            ],
                            [
                                'region' => 'column_2',
                                'type' => 'content',
                                'buttons' => [],
                            ],
                        ],
                        'media' => $this->mediaSlot('column_1_image', $pillarImages[3], 'For The Public'),
                    ]],
                ],
            ],
            [
                'key' => 'about-partners-heading',
                'name' => 'About Us — Our Partners Heading',
                'template_key' => 'minimal',
                'structure' => [
                    'slides' => [[
                        'name' => 'Default',
                        'blocks' => [[
                            'region' => 'main',
                            'type' => 'content',
                            'headline' => 'Our Partners',
                            'buttons' => [],
                        ]],
                        'media' => [],
                    ]],
                ],
            ],
            [
                'key' => 'about-partners-row-1',
                'name' => 'About Us — Partners (1–3)',
                'template_key' => 'three_column_full_width',
                'structure' => [
                    'slides' => [[
                        'name' => 'Default',
                        'blocks' => [
                            ['region' => 'column_1', 'type' => 'content', 'buttons' => []],
                            ['region' => 'column_2', 'type' => 'content', 'buttons' => []],
                            ['region' => 'column_3', 'type' => 'content', 'buttons' => []],
                        ],
                        'media' => array_merge(
                            $this->mediaSlot('column_1_image', $partnerImages[0], 'ECCP'),
                            $this->mediaSlot('column_2_image', $partnerImages[1], 'MAP'),
                            $this->mediaSlot('column_3_image', $partnerImages[2], 'IBPAP'),
                        ),
                    ]],
                ],
            ],
            [
                'key' => 'about-partners-row-2',
                'name' => 'About Us — Partners (4–6)',
                'template_key' => 'three_column_full_width',
                'structure' => [
                    'slides' => [[
                        'name' => 'Default',
                        'blocks' => [
                            ['region' => 'column_1', 'type' => 'content', 'buttons' => []],
                            ['region' => 'column_2', 'type' => 'content', 'buttons' => []],
                            ['region' => 'column_3', 'type' => 'content', 'buttons' => []],
                        ],
                        'media' => array_merge(
                            $this->mediaSlot('column_1_image', $partnerImages[3], 'Inner Wheel'),
                            $this->mediaSlot('column_2_image', $partnerImages[4], 'WomenBizPH'),
                            $this->mediaSlot('column_3_image', $partnerImages[5], 'FIL-CHI BPW'),
                        ),
                    ]],
                ],
            ],
            [
                'key' => 'about-people-heading',
                'name' => 'About Us — Our People Heading',
                'template_key' => 'minimal',
                'structure' => [
                    'slides' => [[
                        'name' => 'Default',
                        'blocks' => [[
                            'region' => 'main',
                            'type' => 'content',
                            'headline' => 'Our People',
                            'buttons' => [],
                        ]],
                        'media' => [],
                    ]],
                ],
            ],
            [
                'key' => 'about-person-sheila',
                'name' => 'About Us — Sheila Lobien',
                'template_key' => 'image_left',
                'structure' => [
                    'slides' => [[
                        'name' => 'Default',
                        'blocks' => [[
                            'region' => 'main',
                            'type' => 'content',
                            'headline' => 'SHEILA LOBIEN',
                            'subheading' => 'CHIEF EXECUTIVE OFFICER',
                            'rich_text' => $this->sheilaBioHtml(),
                            'buttons' => [],
                        ]],
                        'media' => $this->mediaSlot('desktop_image', $sheilaImage, 'Sheila Lobien'),
                    ]],
                ],
            ],
            [
                'key' => 'about-person-alex',
                'name' => 'About Us — Alex Regala',
                'template_key' => 'image_left',
                'structure' => [
                    'slides' => [[
                        'name' => 'Default',
                        'blocks' => [[
                            'region' => 'main',
                            'type' => 'content',
                            'headline' => 'ALEX REGALA',
                            'subheading' => 'DIRECTOR, SALES & STRATEGIC PARTNERSHIPS',
                            'rich_text' => $this->alexBioHtml(),
                            'buttons' => [],
                        ]],
                        'media' => $this->mediaSlot('desktop_image', $alexImage, 'Alex Regala'),
                    ]],
                ],
            ],
            [
                'key' => 'about-person-steph',
                'name' => 'About Us — Steph Ng',
                'template_key' => 'image_left',
                'structure' => [
                    'slides' => [[
                        'name' => 'Default',
                        'blocks' => [[
                            'region' => 'main',
                            'type' => 'content',
                            'headline' => 'STEPH NG',
                            'subheading' => 'ASSOCIATE DIRECTOR',
                            'rich_text' => $this->stephBioHtml(),
                            'buttons' => [],
                        ]],
                        'media' => $this->mediaSlot('desktop_image', $stephImage, 'Steph Ng'),
                    ]],
                ],
            ],
            [
                'key' => 'about-footer-banner',
                'name' => 'About Us — Footer Team Banner',
                'template_key' => 'split_layout',
                'structure' => [
                    'slides' => [[
                        'name' => 'Default',
                        'blocks' => [[
                            'region' => 'main',
                            'type' => 'content',
                            'buttons' => [],
                        ]],
                        'media' => $this->mediaSlot('desktop_image', $footerImage, 'Lobien Realty Group team banner'),
                    ]],
                ],
            ],
        ];
    }

    protected function sheilaBioHtml(): string
    {
        return implode('', [
            '<h4>The Voice of Strategic Real Estate in the Philippines</h4>',
            '<p>Sheila Lobien is the Chief Executive Officer and Founder of Lobien Realty Group (LRG), a powerhouse in Philippine real estate consultancy and property investment. Under her leadership, LRG has redefined the "homegrown" advantage, proving that local insight, paired with global standards, creates a formidable force in commercial leasing, capital investments, and property acquisition.</p>',
            '<h4>A Legacy of Market Leadership</h4>',
            '<p>What began as a visionary venture has evolved into a cornerstone of the Philippine property sector. While global giants rely on distant playbooks, Sheila has steered LRG through a decade of historic shifts, from the digital transformation of the workplace to the resilience of the post-pandemic market. Today, LRG is the firm where the country\'s most significant real estate decisions are made, earning the trust of Fortune 500 multinationals and the Philippines\' leading institutional investors.</p>',
            '<p>Sheila\'s reputation as the foremost female authority in commercial real estate is built on a rare combination of foresight and execution. She is a sought-after thought leader, frequently appearing in The Manila Times and The Philippine Daily Inquirer, and speaking at premier economic forums. Her ability to decode market volatility into actionable investment yields is why serious investors view her not just as a dealmaker, but as a primary strategic advisor and partner.</p>',
            '<h4>The Architect of Modern Office Concepts</h4>',
            '<p>Sheila\'s influence on the Philippine skyline began long before LRG. As a former Regional Director at a Fortune 500 consultancy, she and her team were responsible for leasing over one million square meters of office space worth multi billions of pesos in value. Even more transformative was her tenure as General Manager of Regus Manila, where she pioneered the "plug-and-play" office concept in the early 2000s, a move that fundamentally changed how business is done in the Philippines.</p>',
            '<h4>Empowering the Next Generation of Business</h4>',
            '<p><strong>Beyond the boardroom, Sheila is a tireless advocate for diversity and leadership.</strong></p>',
            '<ul>',
            '<li>Chairperson: Women in Business Committee of the European Chamber of Commerce in the Philippines (ECCP).</li>',
            '<li>Leadership Roles: Active member of the Management Association of the Philippines (MAP) and the Women Business Council.</li>',
            '<li>Global Recognition: Recipient of the ASEAN Women Entrepreneurs Network (AWEN) Award and the World Women Leadership Congress "Woman Super Achiever" Award.</li>',
            '</ul>',
            '<h4>Academic Foundation</h4>',
            '<p><strong>Sheila\'s strategic depth is supported by an elite educational background:</strong></p>',
            '<ul>',
            '<li>BS Tourism – University of the Philippines Diliman</li>',
            '<li>Master of Business Management – University of the Philippines Manila</li>',
            '<li>Executive Programs – Asian Institute of Management (AIM), National University of Singapore (NUS), INSEAD Business School and London School of Economics and Finance.</li>',
            '</ul>',
        ]);
    }

    protected function alexBioHtml(): string
    {
        return implode('', [
            '<p>Alex Regala is a senior commercial real estate and enterprise sales leader with over 25 years of experience advising corporations, institutional landlords, and high-net-worth individuals across the Philippines. As Director of Sales and Strategic Partnerships at Lobien Realty Group, he brings a rigorous, client-first approach to every mandate, whether navigating complex lease negotiations, structuring long-term portfolio strategies, or opening doors to exclusive market opportunities.</p>',
            '<p>Alex has held leadership role at Fortune 500-level organizations, where he was part of pioneers who jumpstarted global delivery hub in the Philippines by one of America\'s biggest financial institutions, and had led commercial leasing and landlord representation, with sales leadership for the manufacturing industry at an executive level. This rare combination of institutional real estate expertise and enterprise-grade sales leadership makes him a trusted advisor for discerning clients who demand precision, discretion, and measurable results.</p>',
            '<p>His approach goes beyond transactions. Alex invests in understanding each client\'s strategic objectives, whether expanding a regional footprint, optimizing an existing portfolio, or securing off-market assets, and delivers solutions that create lasting value.</p>',
            '<h4>AREAS OF PRACTICE</h4>',
            '<ul>',
            '<li>Corporate office and commercial leasing</li>',
            '<li>Landlord and developer representation</li>',
            '<li>Executive-level sales management role for manufacturing and global brand distributorship</li>',
            '<li>Strategic partnerships and business development</li>',
            '<li>Academician and certified professional performance coach</li>',
            '</ul>',
        ]);
    }

    protected function stephBioHtml(): string
    {
        return implode('', [
            '<p>Stephanie Ng is a founding force behind Lobien Realty Group, serving as a Director and a key architect of the firm\'s rapid ascent in the Philippine property sector. With a career defined by high-stakes deal making and institutional rigor, Steph brings a rare end-to-end perspective to commercial real estate—combining the technical precision of an analyst with the closing power of a seasoned negotiator.</p>',
            '<h4>A Legacy of High-Value Delivery</h4>',
            '<p>Before co-building LRG from the ground up, Steph established herself as a top-performing leader at a Fortune 500 property consultancy firm. Her track record is formidable: she has personally closed over 200,000 square meters of office and commercial space. At LRG, she has continued this momentum, spearheading complex multinational corporation (MNC) lease deals and high-value transactions across the Philippines\' primary business districts and emerging hubs.</p>',
            '<h4>The Analytical Edge</h4>',
            '<p>Steph\'s approach is rooted in multi-angle strategy. With a professional background spanning forex and trading, she possesses a sharpened capacity for both fundamental and technical analysis. This allows her to translate volatile market dynamics into clear, actionable solutions for a diverse client base—ranging from homegrown Filipino enterprises to global giants seeking a strategic foothold in the country.</p>',
            '<h4>Academic &amp; International Pedigree</h4>',
            '<p>Steph holds a unique dual degree from De La Salle University, earning both a B.A. in Communication Arts and a B.S. in Business Management. This blend of sharp communication instincts and commercial acumen is further bolstered by international studies at Beijing Language and Culture University. Conversational in Mandarin and Fookien, Steph provides valuable cross-cultural insight for international investors and developers navigating the Philippine market.</p>',
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
        $storagePath = 'media/about/'.md5($relativePath).'.'.$extension;

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
