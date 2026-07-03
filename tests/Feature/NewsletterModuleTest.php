<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Models\InstalledModule;
use App\Models\User;
use App\Modules\Newsletter\Models\NewsletterList;
use App\Modules\Newsletter\Models\NewsletterSend;
use App\Modules\Newsletter\Models\NewsletterSubscriber;
use App\Modules\Newsletter\Notifications\NewsletterNotification;
use App\Modules\PageManager\Models\Page;
use App\Modules\PageManager\Models\PageBlock;
use App\Modules\PageManager\Models\PageMaster;
use App\Modules\PageManager\Models\PageMasterBlock;
use App\Services\Recaptcha\RecaptchaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class NewsletterModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->copyNewsletterModule();
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(base_path('app/Modules/Newsletter'));

        parent::tearDown();
    }

    public function test_install_creates_tables_and_permissions(): void
    {
        $this->installNewsletter();

        $this->assertTrue(Schema::hasTable('newsletter_lists'));
        $this->assertTrue(Schema::hasTable('newsletter_subscribers'));
        $this->assertTrue(Schema::hasTable('newsletter_sends'));
        $this->assertFalse(Schema::hasTable('newsletter_page_attachments'));
        $this->assertFalse(Schema::hasTable('newsletter_section_attachments'));
        $this->assertDatabaseHas('permissions', ['name' => 'newsletter-lists.view']);
        $this->assertDatabaseHas('permissions', ['name' => 'newsletters.send']);
        $this->assertDatabaseHas('newsletter_lists', ['slug' => 'site-updates']);
    }

    public function test_page_with_newsletter_block_includes_list_slug(): void
    {
        $this->installNewsletter();

        $page = Page::updateOrCreate(
            ['path' => '/about'],
            [
                'slug' => 'about',
                'title' => 'About',
                'status' => ContentStatus::Published,
                'published_at' => now(),
            ],
        );

        $page->blocks()->delete();
        PageBlock::create([
            'page_id' => $page->id,
            'region_key' => 'main',
            'block_type' => 'newsletter',
            'sort_order' => 0,
            'config' => ['list_slug' => 'site-updates'],
        ]);

        $this->get('/about')
            ->assertOk()
            ->assertInertia(fn ($inertia) => $inertia
                ->component('Page/Show')
                ->where('regions.main.0.type', 'newsletter')
                ->where('regions.main.0.props.slug', 'site-updates'));
    }

    public function test_homepage_footer_includes_newsletter_from_page_master(): void
    {
        $this->installNewsletter();

        $master = \App\Modules\PageManager\Models\PageMaster::instance();
        $master->blocks()->delete();
        \App\Modules\PageManager\Models\PageMasterBlock::create([
            'page_master_id' => $master->id,
            'region_key' => 'footer',
            'block_type' => 'newsletter',
            'sort_order' => 0,
            'config' => ['list_slug' => 'site-updates'],
        ]);

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn ($inertia) => $inertia
                ->has('regions.footer')
                ->where('regions.footer.0.type', 'newsletter')
                ->where('regions.footer.0.props.slug', 'site-updates'));
    }

    public function test_public_api_returns_active_list_definition(): void
    {
        $this->installNewsletter();

        $response = $this->getJson('/api/newsletters/site-updates');

        $response->assertOk()
            ->assertJsonPath('slug', 'site-updates')
            ->assertJsonPath('auth.logged_in', false)
            ->assertJsonPath('auth.subscribed', false);
    }

    public function test_guest_subscribe_creates_subscriber_row(): void
    {
        $this->installNewsletter();
        $this->disableRecaptcha();

        $response = $this->postJson('/api/newsletters/site-updates/subscribe', [
            'email' => 'jane@example.com',
            'name' => 'Jane Doe',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('auth.subscribed', true);

        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'jane@example.com',
            'status' => NewsletterSubscriber::STATUS_ACTIVE,
        ]);
    }

    public function test_subscribe_requires_recaptcha_when_enabled(): void
    {
        $this->installNewsletter();

        $this->mock(RecaptchaService::class, function ($mock) {
            $mock->shouldReceive('enabled')->andReturn(true);
        });

        $response = $this->postJson('/api/newsletters/site-updates/subscribe', [
            'email' => 'jane@example.com',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['recaptcha_token']);
    }

    public function test_logged_in_user_subscribe_and_unsubscribe_toggle_state(): void
    {
        $this->installNewsletter();
        $this->disableRecaptcha();

        $admin = User::where('email', 'admin@fyd.local')->first();

        $this->actingAs($admin)
            ->postJson('/api/newsletters/site-updates/subscribe')
            ->assertCreated()
            ->assertJsonPath('auth.subscribed', true);

        $this->actingAs($admin)
            ->getJson('/api/newsletters/site-updates')
            ->assertJsonPath('auth.subscribed', true);

        $this->actingAs($admin)
            ->postJson('/api/newsletters/site-updates/unsubscribe')
            ->assertOk()
            ->assertJsonPath('auth.subscribed', false);
    }

    public function test_duplicate_email_reactivates_subscription(): void
    {
        $this->installNewsletter();
        $this->disableRecaptcha();

        $this->postJson('/api/newsletters/site-updates/subscribe', [
            'email' => 'jane@example.com',
        ])->assertCreated();

        $subscriber = NewsletterSubscriber::where('email', 'jane@example.com')->first();
        $subscriber->update([
            'status' => NewsletterSubscriber::STATUS_UNSUBSCRIBED,
            'unsubscribed_at' => now(),
        ]);

        $this->postJson('/api/newsletters/site-updates/subscribe', [
            'email' => 'jane@example.com',
        ])->assertCreated();

        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'jane@example.com',
            'status' => NewsletterSubscriber::STATUS_ACTIVE,
        ]);
        $this->assertSame(1, NewsletterSubscriber::where('email', 'jane@example.com')->count());
    }

    public function test_token_unsubscribe_shows_confirmation_page(): void
    {
        $this->installNewsletter();
        $this->disableRecaptcha();

        $this->postJson('/api/newsletters/site-updates/subscribe', [
            'email' => 'jane@example.com',
        ]);

        $subscriber = NewsletterSubscriber::where('email', 'jane@example.com')->first();

        $response = $this->get(route('newsletters.unsubscribe', ['token' => $subscriber->unsubscribe_token]));

        $response->assertOk()
            ->assertSee('You have been unsubscribed', false)
            ->assertSee('jane@example.com', false);

        $this->assertDatabaseHas('newsletter_subscribers', [
            'id' => $subscriber->id,
            'status' => NewsletterSubscriber::STATUS_UNSUBSCRIBED,
        ]);
    }

    public function test_admin_export_returns_csv(): void
    {
        $this->installNewsletter();
        $this->disableRecaptcha();

        $this->postJson('/api/newsletters/site-updates/subscribe', [
            'email' => 'jane@example.com',
        ]);

        $admin = User::where('email', 'admin@fyd.local')->first();

        $response = $this->actingAs($admin)->get('/admin/newsletter-subscribers/export');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString('jane@example.com', $response->streamedContent());
    }

    public function test_send_queues_notifications_with_unsubscribe_url(): void
    {
        $this->installNewsletter();
        $this->disableRecaptcha();
        Notification::fake();

        $this->postJson('/api/newsletters/site-updates/subscribe', [
            'email' => 'jane@example.com',
        ]);

        $admin = User::where('email', 'admin@fyd.local')->first();
        $list = NewsletterList::where('slug', 'site-updates')->first();

        $response = $this->actingAs($admin)->post('/admin/newsletters/send', [
            'newsletter_list_id' => $list->id,
            'subject' => 'Hello subscribers',
            'body' => '<p>Welcome!</p>',
        ]);

        $response->assertRedirect(route('admin.newsletter-sends.index'));

        $subscriber = NewsletterSubscriber::where('email', 'jane@example.com')->first();

        Notification::assertSentTo(
            $subscriber,
            NewsletterNotification::class,
            function (NewsletterNotification $notification) use ($subscriber) {
                return str_contains($notification->unsubscribeUrl, $subscriber->unsubscribe_token);
            },
        );

        $this->assertDatabaseHas('newsletter_sends', [
            'newsletter_list_id' => $list->id,
            'subject' => 'Hello subscribers',
            'recipient_count' => 1,
        ]);
    }

    public function test_uninstall_removes_newsletter_blocks_from_page_regions(): void
    {
        $this->installNewsletter();

        $page = Page::updateOrCreate(
            ['path' => '/contact'],
            [
                'slug' => 'contact',
                'title' => 'Contact',
                'status' => ContentStatus::Published,
                'published_at' => now(),
            ],
        );

        $page->blocks()->delete();
        PageBlock::create([
            'page_id' => $page->id,
            'region_key' => 'footer',
            'block_type' => 'newsletter',
            'sort_order' => 0,
            'config' => ['list_slug' => 'site-updates'],
        ]);

        $master = PageMaster::instance();
        PageMasterBlock::create([
            'page_master_id' => $master->id,
            'region_key' => 'footer',
            'block_type' => 'newsletter',
            'sort_order' => 0,
            'config' => ['list_slug' => 'site-updates'],
        ]);

        $this->assertDatabaseHas('page_blocks', [
            'page_id' => $page->id,
            'block_type' => 'newsletter',
        ]);
        $this->assertDatabaseHas('page_master_blocks', [
            'page_master_id' => $master->id,
            'block_type' => 'newsletter',
        ]);

        Artisan::call('module:uninstall', ['name' => 'Newsletter', '--force' => true]);

        $this->assertDatabaseMissing('page_blocks', [
            'page_id' => $page->id,
            'block_type' => 'newsletter',
        ]);
        $this->assertDatabaseMissing('page_master_blocks', [
            'page_master_id' => $master->id,
            'block_type' => 'newsletter',
        ]);
    }

    protected function installNewsletter(): void
    {
        if (! InstalledModule::where('name', 'Newsletter')->where('status', InstalledModule::STATUS_INSTALLED)->exists()) {
            $this->seed();
            Artisan::call('module:install', ['name' => 'Newsletter', '--force' => true]);
        }

        $this->registerModuleRoutes();
        $this->registerModuleViews();
    }

    protected function disableRecaptcha(): void
    {
        $this->mock(RecaptchaService::class, function ($mock) {
            $mock->shouldReceive('enabled')->andReturn(false);
        });
    }

    protected function registerModuleViews(): void
    {
        $viewsPath = base_path('app/Modules/Newsletter/Views');

        if (is_dir($viewsPath)) {
            View::addNamespace('newsletter', $viewsPath);
        }
    }

    protected function registerModuleRoutes(): void
    {
        $modulesPath = config('modules.path');
        $module = 'Newsletter';

        $webRoutes = "{$modulesPath}/{$module}/Routes/web.php";

        if (file_exists($webRoutes)) {
            Route::middleware('web')->group($webRoutes);
        }

        $adminRoutes = "{$modulesPath}/{$module}/Routes/admin.php";

        if (file_exists($adminRoutes)) {
            Route::middleware(array_merge(config('fyd.admin.middleware'), ['admin.access']))
                ->prefix(config('fyd.admin.prefix'))
                ->name('admin.')
                ->group($adminRoutes);
        }

        Route::getRoutes()->refreshNameLookups();
    }

    protected function copyNewsletterModule(): void
    {
        $source = base_path('contrib/Newsletter');
        $target = base_path('app/Modules/Newsletter');

        if (File::isDirectory($target)) {
            File::deleteDirectory($target);
        }

        File::copyDirectory($source, $target);
    }
}
