<?php

namespace Database\Seeders;

use App\Enums\BannerType;
use App\Enums\ContentStatus;
use App\Enums\MenuLocation;
use App\Models\User;
use App\Modules\Banners\Models\Banner;
use App\Modules\Banners\Models\BannerTemplate;
use App\Modules\Banners\Services\BannerFormSchemaService;
use App\Modules\Banners\Services\BannerService;
use App\Modules\Content\Models\Content;
use App\Modules\Menus\Models\Menu;
use App\Modules\Menus\Models\MenuItem;
use Illuminate\Database\Seeder;

/**
 * Seeds sample pages, articles, banners, and menus for new projects.
 *
 * Uses generic "Your Website" branding. Requires AuthenticationSeeder to run first.
 * Safe to skip on bare installs by removing from DatabaseSeeder.
 *
 * @see docs/SEEDING.md
 */
class SampleContentSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@fyd.local')->firstOrFail();

        $this->seedContent($admin);
        $this->seedBanners();
        $this->seedMenus();
    }

    protected function seedContent(User $admin): void
    {
        $siteName = 'Your Website';

        $pages = [
            [
                'title' => 'About Us',
                'slug' => 'about',
                'summary' => 'Learn about our mission, values, and the team behind '.$siteName.'.',
                'body' => '<p>We help businesses build professional online presences.</p><p>Every company deserves a website that reflects their brand and drives results. Our platform combines powerful content management with beautiful design.</p>',
                'seo' => [
                    'seo_title' => 'About Us — '.$siteName,
                    'meta_description' => 'Learn about '.$siteName.' and our mission to deliver exceptional digital experiences.',
                ],
            ],
            [
                'title' => 'Services',
                'slug' => 'services',
                'summary' => 'Explore our range of professional services for your business.',
                'body' => '<p>We offer a comprehensive suite of services designed to help your business grow:</p><ul><li>Website Development</li><li>Content Management Solutions</li><li>Digital Marketing Strategy</li><li>SEO Optimization</li><li>Ongoing Support &amp; Maintenance</li></ul>',
                'seo' => [
                    'seo_title' => 'Services — '.$siteName,
                    'meta_description' => 'Explore our professional services including web development, CMS, and digital marketing.',
                ],
            ],
            [
                'title' => 'Contact',
                'slug' => 'contact',
                'summary' => 'Get in touch with our team.',
                'body' => '<p>We would love to hear from you. Reach out to discuss your project or ask any questions.</p>',
                'seo' => [
                    'seo_title' => 'Contact — '.$siteName,
                    'meta_description' => 'Contact '.$siteName.' to discuss your next project.',
                ],
            ],
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'summary' => 'Our privacy policy and data handling practices.',
                'body' => '<p>This Privacy Policy describes how '.$siteName.' collects, uses, and protects your personal information.</p><p>We are committed to protecting your privacy and ensuring the security of your data.</p>',
                'seo' => ['seo_title' => 'Privacy Policy — '.$siteName, 'meta_description' => 'Read our privacy policy.'],
            ],
            [
                'title' => 'Terms of Service',
                'slug' => 'terms-of-service',
                'summary' => 'Terms and conditions for using our services.',
                'body' => '<p>By using '.$siteName.' services, you agree to these terms and conditions.</p><p>Please read them carefully before using our platform.</p>',
                'seo' => ['seo_title' => 'Terms of Service — '.$siteName, 'meta_description' => 'Read our terms of service.'],
            ],
        ];

        foreach ($pages as $data) {
            $content = Content::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'content_type' => 'page',
                    'title' => $data['title'],
                    'summary' => $data['summary'],
                    'body' => $data['body'],
                    'status' => ContentStatus::Published,
                    'published_at' => now()->subDays(rand(1, 30)),
                    'author_id' => $admin->id,
                ]
            );

            $content->saveSeo($data['seo']);
        }

        $articles = [
            [
                'title' => 'Welcome to Your Website',
                'slug' => 'welcome-to-your-website',
                'summary' => 'Introducing your new website.',
                'body' => '<p>We are excited to launch this website built on the FYD Laravel Bootstrap CMS platform.</p><p>This launch represents a commitment to delivering modern, fast, and maintainable web experiences.</p>',
            ],
            [
                'title' => '5 Tips for Better Website Content',
                'slug' => '5-tips-for-better-website-content',
                'summary' => 'Practical advice for creating content that engages your audience.',
                'body' => '<p>Great website content is clear, concise, and focused on your audience\'s needs.</p><ol><li>Know your audience</li><li>Use clear headlines</li><li>Keep paragraphs short</li><li>Include calls to action</li><li>Update content regularly</li></ol>',
            ],
            [
                'title' => 'Why Bootstrap for Corporate Websites',
                'slug' => 'why-bootstrap-for-corporate-websites',
                'summary' => 'Bootstrap remains the top choice for professional business websites.',
                'body' => '<p>Bootstrap provides a proven, accessible, and responsive foundation for corporate websites.</p><p>With its extensive component library and consistent design language, Bootstrap helps teams ship professional sites faster without sacrificing quality.</p>',
            ],
            [
                'title' => 'Getting Started with the CMS',
                'slug' => 'getting-started-with-the-cms',
                'summary' => 'A quick guide to managing your content.',
                'body' => '<p>Content management is straightforward.</p><p>Log in to the admin portal, navigate to Content, and start creating. Configure SEO settings and publish when ready.</p>',
            ],
        ];

        foreach ($articles as $index => $data) {
            $content = Content::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'content_type' => 'article',
                    'title' => $data['title'],
                    'summary' => $data['summary'],
                    'body' => $data['body'],
                    'status' => ContentStatus::Published,
                    'published_at' => now()->subDays($index + 1),
                    'author_id' => $admin->id,
                ]
            );

            $content->saveSeo([
                'seo_title' => $data['title'].' — '.$siteName,
                'meta_description' => $data['summary'],
            ]);
        }
    }

    protected function seedBanners(): void
    {
        $bannerService = app(BannerService::class);
        $sortOrder = 0;

        foreach (BannerTemplate::query()->orderBy('sort_order')->get() as $template) {
            $type = $template->key === 'image_carousel' ? BannerType::Carousel : BannerType::Hero;
            $effectSettings = $template->key === 'image_carousel'
                ? ['effect' => 'slide', 'speed' => 600, 'delay' => 5000, 'loop' => true, 'autoplay' => true]
                : ['effect' => 'none', 'speed' => 500, 'delay' => 0, 'loop' => false, 'autoplay' => false];

            $banner = Banner::updateOrCreate(
                ['key' => 'sample-'.$template->key],
                [
                    'name' => 'Sample: '.$template->name,
                    'title' => $template->name,
                    'subtitle' => 'Sample banner',
                    'description' => $template->description ?? 'Sample content for the '.$template->name.' template.',
                    'type' => $type,
                    'template_id' => $template->id,
                    'sort_order' => $sortOrder++,
                    'effect_settings' => $effectSettings,
                    'status' => ContentStatus::Published,
                ]
            );

            $bannerService->syncStructure($banner, array_merge(
                ['template_id' => $template->id],
                $this->buildSampleStructure($template),
            ));
        }
    }

    protected function buildSampleStructure(BannerTemplate $template): array
    {
        $schemaService = app(BannerFormSchemaService::class);
        $schema = $schemaService->resolve($template);

        if ($schemaService->supportsManySlides($schema)) {
            return [
                'slides' => [
                    $this->makeSlide('Slide 1', $template, $schema, 1),
                    $this->makeSlide('Slide 2', $template, $schema, 2),
                ],
            ];
        }

        $blocks = [];
        foreach ($schema['blocks'] as $blockSchema) {
            $blocks[] = $this->makeBlock(
                $blockSchema['region'] ?? 'main',
                $template,
                $schema,
                $blockSchema,
            );
        }

        return [
            'slides' => [[
                'name' => 'Default',
                'blocks' => $blocks,
                'media' => [],
            ]],
        ];
    }

    protected function makeSlide(string $name, BannerTemplate $template, array $schema, int $number): array
    {
        $blocks = [];
        foreach ($schema['blocks'] as $blockSchema) {
            $blocks[] = $this->makeBlock(
                $blockSchema['region'] ?? 'main',
                $template,
                $schema,
                $blockSchema,
                $number,
            );
        }

        return [
            'name' => $name,
            'blocks' => $blocks,
            'media' => [],
        ];
    }

    protected function makeBlock(
        string $region,
        BannerTemplate $template,
        array $schema,
        array $blockSchema,
        int $slideNumber = 1,
    ): array {
        $columnNumber = $this->columnNumber($region);

        if ($columnNumber !== null) {
            $headline = 'Column '.$columnNumber;
        } elseif ($template->key === 'image_carousel') {
            $headline = 'Slide '.$slideNumber;
        } else {
            $headline = $template->name;
        }

        $block = [
            'region' => $region,
            'type' => 'content',
            'headline' => $headline,
            'subheading' => 'Sample subheading',
            'description' => 'Sample content for the '.$template->name.' template.',
            'buttons' => [],
        ];

        $buttonCount = (int) ($schema['buttons']['count'] ?? 0);
        if ($buttonCount > 0 && ($region === 'main' || $columnNumber !== null)) {
            $block['buttons'][] = [
                'label' => 'Learn More',
                'url' => '/about',
                'target' => '_self',
                'style' => 'primary',
            ];
        }

        return $block;
    }

    protected function columnNumber(string $region): ?int
    {
        if (! str_starts_with($region, 'column_')) {
            return null;
        }

        return (int) str_replace('column_', '', $region);
    }

    protected function seedMenus(): void
    {
        $header = Menu::updateOrCreate(
            ['location' => MenuLocation::Header],
            ['name' => 'Main Navigation']
        );

        $header->allItems()->delete();
        $headerItems = [
            ['title' => 'Home', 'url' => '/', 'sort_order' => 0],
            ['title' => 'About', 'url' => '/about', 'sort_order' => 1],
            ['title' => 'Services', 'url' => '/services', 'sort_order' => 2],
            ['title' => 'Contact', 'url' => '/contact', 'sort_order' => 3],
        ];

        foreach ($headerItems as $item) {
            MenuItem::create([
                'menu_id' => $header->id,
                'title' => $item['title'],
                'url' => $item['url'],
                'link_type' => 'internal',
                'target' => '_self',
                'sort_order' => $item['sort_order'],
            ]);
        }

        $footer = Menu::updateOrCreate(
            ['location' => MenuLocation::Footer],
            ['name' => 'Footer Navigation']
        );

        $footer->allItems()->delete();
        $footerItems = [
            ['title' => 'About', 'url' => '/about', 'sort_order' => 0],
            ['title' => 'Services', 'url' => '/services', 'sort_order' => 1],
            ['title' => 'Contact', 'url' => '/contact', 'sort_order' => 2],
            ['title' => 'Privacy Policy', 'url' => '/privacy-policy', 'sort_order' => 3],
            ['title' => 'Terms of Service', 'url' => '/terms-of-service', 'sort_order' => 4],
        ];

        foreach ($footerItems as $item) {
            MenuItem::create([
                'menu_id' => $footer->id,
                'title' => $item['title'],
                'url' => $item['url'],
                'link_type' => 'internal',
                'target' => '_self',
                'sort_order' => $item['sort_order'],
            ]);
        }
    }
}
