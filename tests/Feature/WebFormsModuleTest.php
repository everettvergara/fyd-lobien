<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Models\InstalledModule;
use App\Models\User;
use App\Modules\PageManager\Models\Page;
use App\Modules\PageManager\Models\PageBlock;
use App\Modules\WebForms\Models\Webform;
use App\Modules\WebForms\Models\WebformSubmission;
use App\Services\Recaptcha\RecaptchaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class WebFormsModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->copyWebFormsModule();
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(base_path('app/Modules/WebForms'));

        parent::tearDown();
    }

    public function test_install_creates_tables_and_permissions(): void
    {
        $this->installWebForms();

        $this->assertTrue(Schema::hasTable('webforms'));
        $this->assertTrue(Schema::hasTable('webform_submissions'));
        $this->assertFalse(Schema::hasTable('webform_page_attachments'));
        $this->assertFalse(Schema::hasColumn('webforms', 'content_slug'));
        $this->assertDatabaseHas('permissions', ['name' => 'webforms.view']);
        $this->assertDatabaseHas('permissions', ['name' => 'webforms.submissions.view']);
        $this->assertDatabaseHas('webforms', ['slug' => 'contact']);
    }

    public function test_public_api_returns_active_form_definition(): void
    {
        $this->installWebForms();

        $response = $this->getJson('/api/webforms/contact');

        $response->assertOk()
            ->assertJsonPath('slug', 'contact')
            ->assertJsonPath('fields.0.key', 'name');
    }

    public function test_public_submit_stores_json_submission(): void
    {
        $this->installWebForms();

        $this->mock(RecaptchaService::class, function ($mock) {
            $mock->shouldReceive('enabled')->andReturn(false);
        });

        $response = $this->postJson('/api/webforms/contact/submit', [
            'fields' => [
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
                'inquiry_type' => 'general',
                'preferred_date' => '2026-07-10',
                'message' => 'Hello there',
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('webform_submissions', [
            'webform_id' => Webform::where('slug', 'contact')->value('id'),
        ]);

        $submission = WebformSubmission::first();
        $this->assertSame('Jane Doe', $submission->data['name']);
        $this->assertSame('jane@example.com', $submission->data['email']);
    }

    public function test_public_submit_validates_required_fields(): void
    {
        $this->installWebForms();

        $this->mock(RecaptchaService::class, function ($mock) {
            $mock->shouldReceive('enabled')->andReturn(false);
        });

        $response = $this->postJson('/api/webforms/contact/submit', [
            'fields' => [
                'name' => '',
                'email' => 'not-an-email',
                'message' => '',
            ],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['fields.name', 'fields.email', 'fields.message']);
    }

    public function test_admin_can_view_submissions_list(): void
    {
        $this->installWebForms();

        $admin = User::where('email', 'admin@fyd.local')->first();

        $response = $this->actingAs($admin)->get('/admin/webform-submissions');

        $response->assertOk();
    }

    public function test_page_with_webform_block_includes_form_slug(): void
    {
        $this->installWebForms();

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
            'region_key' => 'main',
            'block_type' => 'webform',
            'sort_order' => 0,
            'config' => ['webform_slug' => 'contact'],
        ]);

        $this->get('/contact')
            ->assertOk()
            ->assertInertia(fn ($inertia) => $inertia
                ->component('Page/Show')
                ->where('regions.main.0.type', 'webform')
                ->where('regions.main.0.props.slug', 'contact'));
    }

    public function test_builder_updates_schema(): void
    {
        $this->installWebForms();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $webform = Webform::where('slug', 'contact')->first();

        $schema = $webform->schema;
        $schema['fields'][] = [
            'key' => 'phone',
            'type' => 'tel',
            'label' => 'Phone',
            'placeholder' => '',
            'help' => '',
            'required' => false,
            'options' => [],
            'validation' => ['min' => null, 'max' => 20],
        ];

        $response = $this->actingAs($admin)->put('/admin/webforms/'.$webform->id.'/builder', [
            'schema' => $schema,
        ]);

        $response->assertRedirect();
        $this->assertTrue(collect($webform->fresh()->fieldDefinitions())->contains(fn ($field) => $field['key'] === 'phone'));
    }

    public function test_builder_page_renders_existing_fields(): void
    {
        $this->installWebForms();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $webform = Webform::where('slug', 'contact')->first();

        $response = $this->actingAs($admin)->get('/admin/webforms/'.$webform->id.'/builder');

        $response->assertOk()
            ->assertSee('Email Address', false);
    }

    public function test_builder_can_update_existing_field_label(): void
    {
        $this->installWebForms();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $webform = Webform::where('slug', 'contact')->first();
        $schema = $webform->schema;

        foreach ($schema['fields'] as &$field) {
            if ($field['key'] === 'email') {
                $field['label'] = 'Work Email';
            }
        }
        unset($field);

        $response = $this->actingAs($admin)->put('/admin/webforms/'.$webform->id.'/builder', [
            'schema' => $schema,
        ]);

        $response->assertRedirect();

        $emailField = collect($webform->fresh()->fieldDefinitions())->firstWhere('key', 'email');
        $this->assertSame('Work Email', $emailField['label']);
    }

    public function test_builder_preserves_edits_on_validation_error(): void
    {
        $this->installWebForms();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $webform = Webform::where('slug', 'contact')->first();
        $schema = $webform->schema;
        $schema['fields'][] = [
            'key' => 'email',
            'type' => 'text',
            'label' => 'Duplicate Email Key',
            'placeholder' => '',
            'help' => '',
            'required' => false,
            'options' => [],
            'validation' => ['min' => null, 'max' => null],
        ];

        $this->actingAs($admin);

        $response = $this->from('/admin/webforms/'.$webform->id.'/builder')
            ->followingRedirects()
            ->put('/admin/webforms/'.$webform->id.'/builder', [
                'schema' => $schema,
            ]);

        $response->assertOk()
            ->assertSee('Duplicate Email Key', false)
            ->assertSee('Could not save form fields', false);
    }

    public function test_edit_webform_page_links_to_builder(): void
    {
        $this->installWebForms();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $webform = Webform::where('slug', 'contact')->first();

        $response = $this->actingAs($admin)->get('/admin/webforms/'.$webform->id.'/edit');

        $response->assertOk()
            ->assertSee(route('admin.webforms.builder', $webform), false)
            ->assertSee('Form Builder', false);
    }

    public function test_uninstall_removes_tables_and_permissions(): void
    {
        $this->installWebForms();

        Artisan::call('module:uninstall', ['name' => 'WebForms', '--force' => true]);

        $this->assertFalse(Schema::hasTable('webforms'));
        $this->assertFalse(Schema::hasTable('webform_submissions'));
        $this->assertDatabaseMissing('permissions', ['name' => 'webforms.view']);
        $this->assertDatabaseMissing('permissions', ['name' => 'webforms.submissions.view']);
        $this->assertDatabaseMissing('installed_modules', ['name' => 'WebForms']);
    }

    public function test_uninstall_removes_webform_blocks_from_page_regions(): void
    {
        $this->installWebForms();

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
            'region_key' => 'main',
            'block_type' => 'webform',
            'sort_order' => 0,
            'config' => ['webform_slug' => 'contact'],
        ]);

        $this->assertDatabaseHas('page_blocks', [
            'page_id' => $page->id,
            'block_type' => 'webform',
        ]);

        Artisan::call('module:uninstall', ['name' => 'WebForms', '--force' => true]);

        $this->assertDatabaseMissing('page_blocks', [
            'page_id' => $page->id,
            'block_type' => 'webform',
        ]);
    }

    protected function installWebForms(): void
    {
        if (! InstalledModule::where('name', 'WebForms')->where('status', InstalledModule::STATUS_INSTALLED)->exists()) {
            $this->seed();
            Artisan::call('module:install', ['name' => 'WebForms', '--force' => true]);
        }

        $this->registerModuleRoutes();
        $this->registerModuleViews();
    }

    protected function registerModuleViews(): void
    {
        $viewsPath = base_path('app/Modules/WebForms/Views');

        if (is_dir($viewsPath)) {
            View::addNamespace('webforms', $viewsPath);
        }
    }

    protected function registerModuleRoutes(): void
    {
        $modulesPath = config('modules.path');
        $module = 'WebForms';

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

    protected function copyWebFormsModule(): void
    {
        $source = base_path('contrib/WebForms');
        $target = base_path('app/Modules/WebForms');

        if (File::isDirectory($target)) {
            File::deleteDirectory($target);
        }

        File::copyDirectory($source, $target);
    }
}
