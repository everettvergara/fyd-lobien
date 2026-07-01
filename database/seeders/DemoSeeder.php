<?php

namespace Database\Seeders;

use App\Enums\BannerPlacement;
use App\Enums\BannerType;
use App\Enums\ContentStatus;
use App\Enums\MenuLocation;
use App\Enums\UserStatus;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Modules\Banners\Models\Banner;
use App\Modules\Menus\Models\Menu;
use App\Modules\Menus\Models\MenuItem;
use App\Modules\Pages\Models\Page;
use App\Modules\Pages\Models\PageSection;
use App\Modules\Posts\Models\Post;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@fyd.local')->firstOrFail();

        $this->seedDemoUsers();
        $this->seedSettings();
        $this->seedPages($admin);
        $this->seedPosts($admin);
        $this->seedBanners();
        $this->seedMenus();
    }

    protected function seedDemoUsers(): void
    {
        $users = [
            ['email' => 'editor@fyd.local', 'name' => 'Demo Editor', 'role' => 'editor'],
            ['email' => 'author@fyd.local', 'name' => 'Demo Author', 'role' => 'author'],
            ['email' => 'viewer@fyd.local', 'name' => 'Demo Viewer', 'role' => 'viewer'],
        ];

        foreach ($users as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'status' => UserStatus::Active,
                    'email_verified_at' => now(),
                ]
            );

            if ($role = Role::where('name', $data['role'])->first()) {
                $user->syncRoles([$role->id]);
            }
        }
    }

    protected function seedSettings(): void
    {
        $settings = [
            'general' => [
                'website_name' => 'FYD Corporate',
                'tagline' => 'Building exceptional digital experiences',
            ],
            'contact' => [
                'email' => 'hello@fydcorporate.com',
                'phone' => '+1 (555) 123-4567',
                'address' => '123 Business Avenue, Suite 100, New York, NY 10001',
            ],
            'social' => [
                'facebook' => 'https://facebook.com',
                'twitter' => 'https://twitter.com',
                'instagram' => 'https://instagram.com',
                'linkedin' => 'https://linkedin.com',
            ],
            'seo' => [
                'default_title' => 'FYD Corporate — Professional Business Solutions',
                'default_description' => 'FYD Corporate delivers professional websites, marketing solutions, and digital services for growing businesses.',
            ],
        ];

        foreach ($settings as $group => $items) {
            foreach ($items as $key => $value) {
                Setting::set($group, $key, $value);
            }
        }
    }

    protected function seedPages(User $admin): void
    {
        $pages = [
            [
                'title' => 'About Us',
                'slug' => 'about',
                'summary' => 'Learn about our mission, values, and the team behind FYD Corporate.',
                'content' => "Founded with a vision to simplify digital excellence, FYD Corporate helps businesses build professional online presences.\n\nWe believe every company deserves a website that reflects their brand and drives results. Our platform combines powerful content management with beautiful design.",
                'sections' => [
                    ['component_type' => 'feature_grid', 'settings' => ['title' => 'Our Values', 'subtitle' => 'What drives us every day']],
                    ['component_type' => 'statistics', 'settings' => ['title' => 'By the Numbers']],
                ],
                'seo' => [
                    'seo_title' => 'About Us — FYD Corporate',
                    'meta_description' => 'Learn about FYD Corporate and our mission to deliver exceptional digital experiences.',
                ],
            ],
            [
                'title' => 'Services',
                'slug' => 'services',
                'summary' => 'Explore our range of professional services for your business.',
                'content' => "We offer a comprehensive suite of services designed to help your business grow:\n\n• Corporate Website Development\n• Content Management Solutions\n• Digital Marketing Strategy\n• SEO Optimization\n• Ongoing Support & Maintenance",
                'sections' => [
                    ['component_type' => 'feature_grid', 'settings' => ['title' => 'Our Services', 'subtitle' => 'Solutions tailored to your needs']],
                    ['component_type' => 'cta', 'settings' => ['title' => 'Ready to get started?', 'button_text' => 'Contact Us', 'button_url' => '/contact']],
                ],
                'seo' => [
                    'seo_title' => 'Services — FYD Corporate',
                    'meta_description' => 'Explore our professional services including web development, CMS, and digital marketing.',
                ],
            ],
            [
                'title' => 'Contact',
                'slug' => 'contact',
                'summary' => 'Get in touch with our team.',
                'content' => "We would love to hear from you. Reach out to discuss your project or ask any questions.",
                'sections' => [
                    ['component_type' => 'contact', 'settings' => ['title' => 'Contact Us', 'subtitle' => 'We are here to help']],
                ],
                'seo' => [
                    'seo_title' => 'Contact — FYD Corporate',
                    'meta_description' => 'Contact FYD Corporate to discuss your next project.',
                ],
            ],
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'summary' => 'Our privacy policy and data handling practices.',
                'content' => "This Privacy Policy describes how FYD Corporate collects, uses, and protects your personal information.\n\nWe are committed to protecting your privacy and ensuring the security of your data.",
                'sections' => [],
                'seo' => ['seo_title' => 'Privacy Policy — FYD Corporate', 'meta_description' => 'Read our privacy policy.'],
            ],
            [
                'title' => 'Terms of Service',
                'slug' => 'terms-of-service',
                'summary' => 'Terms and conditions for using our services.',
                'content' => "By using FYD Corporate services, you agree to these terms and conditions.\n\nPlease read them carefully before using our platform.",
                'sections' => [],
                'seo' => ['seo_title' => 'Terms of Service — FYD Corporate', 'meta_description' => 'Read our terms of service.'],
            ],
        ];

        foreach ($pages as $data) {
            $page = Page::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'title' => $data['title'],
                    'summary' => $data['summary'],
                    'content' => $data['content'],
                    'status' => ContentStatus::Published,
                    'published_at' => now()->subDays(rand(1, 30)),
                    'author_id' => $admin->id,
                    'template' => 'default',
                ]
            );

            $page->sections()->delete();
            foreach ($data['sections'] as $index => $section) {
                PageSection::create([
                    'page_id' => $page->id,
                    'component_type' => $section['component_type'],
                    'sort_order' => $index,
                    'settings' => $section['settings'],
                ]);
            }

            $page->saveSeo($data['seo']);
        }
    }

    protected function seedPosts(User $admin): void
    {
        $posts = [
            [
                'title' => 'Welcome to FYD Corporate',
                'slug' => 'welcome-to-fyd-corporate',
                'excerpt' => 'Introducing our new corporate website powered by FYD CMS.',
                'content' => "We are excited to launch our new website built on the FYD Laravel Bootstrap CMS platform.\n\nThis launch represents our commitment to delivering modern, fast, and maintainable web experiences. The new site features a responsive design, integrated blog, and powerful content management tools.",
            ],
            [
                'title' => '5 Tips for Better Website Content',
                'slug' => '5-tips-for-better-website-content',
                'excerpt' => 'Practical advice for creating content that engages your audience.',
                'content' => "Great website content is clear, concise, and focused on your audience's needs.\n\n1. Know your audience\n2. Use clear headlines\n3. Keep paragraphs short\n4. Include calls to action\n5. Update content regularly",
            ],
            [
                'title' => 'Why Bootstrap for Corporate Websites',
                'slug' => 'why-bootstrap-for-corporate-websites',
                'excerpt' => 'Bootstrap remains the top choice for professional business websites.',
                'content' => "Bootstrap provides a proven, accessible, and responsive foundation for corporate websites.\n\nWith its extensive component library and consistent design language, Bootstrap helps teams ship professional sites faster without sacrificing quality.",
            ],
            [
                'title' => 'Getting Started with FYD CMS',
                'slug' => 'getting-started-with-fyd-cms',
                'excerpt' => 'A quick guide to managing your content with FYD CMS.',
                'content' => "FYD CMS makes content management straightforward.\n\nLog in to the admin portal, navigate to Pages or Posts, and start creating. Use the page builder to add sections, configure SEO settings, and publish when ready.",
            ],
        ];

        foreach ($posts as $index => $data) {
            $post = Post::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'title' => $data['title'],
                    'excerpt' => $data['excerpt'],
                    'content' => $data['content'],
                    'summary' => $data['excerpt'],
                    'status' => ContentStatus::Published,
                    'published_at' => now()->subDays($index + 1),
                    'author_id' => $admin->id,
                ]
            );

            $post->saveSeo([
                'seo_title' => $data['title'].' — FYD Corporate Blog',
                'meta_description' => $data['excerpt'],
            ]);
        }
    }

    protected function seedBanners(): void
    {
        $banners = [
            [
                'name' => 'Homepage Hero',
                'title' => 'Build Your Digital Future',
                'subtitle' => 'FYD Corporate',
                'description' => 'Professional websites and content management for growing businesses.',
                'type' => BannerType::Hero,
                'placement' => BannerPlacement::HomepageHero,
                'button_text' => 'Our Services',
                'button_url' => '/services',
                'sort_order' => 0,
            ],
            [
                'name' => 'Slider — Innovation',
                'title' => 'Innovation Meets Simplicity',
                'subtitle' => 'Featured',
                'description' => 'Modern tools for modern businesses.',
                'type' => BannerType::Carousel,
                'placement' => BannerPlacement::HomepageSlider,
                'button_text' => 'Learn More',
                'button_url' => '/about',
                'sort_order' => 0,
            ],
            [
                'name' => 'Slider — Growth',
                'title' => 'Grow Your Business Online',
                'subtitle' => 'Solutions',
                'description' => 'From landing pages to full corporate sites.',
                'type' => BannerType::Carousel,
                'placement' => BannerPlacement::HomepageSlider,
                'button_text' => 'Contact Us',
                'button_url' => '/contact',
                'sort_order' => 1,
            ],
        ];

        foreach ($banners as $data) {
            Banner::updateOrCreate(
                ['name' => $data['name']],
                [
                    ...$data,
                    'status' => ContentStatus::Published,
                    'published_at' => now(),
                ]
            );
        }
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
            ['title' => 'Blog', 'url' => '/blog', 'sort_order' => 3],
            ['title' => 'Contact', 'url' => '/contact', 'sort_order' => 4],
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
            ['title' => 'Blog', 'url' => '/blog', 'sort_order' => 2],
            ['title' => 'Contact', 'url' => '/contact', 'sort_order' => 3],
            ['title' => 'Privacy Policy', 'url' => '/privacy-policy', 'sort_order' => 4],
            ['title' => 'Terms of Service', 'url' => '/terms-of-service', 'sort_order' => 5],
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
