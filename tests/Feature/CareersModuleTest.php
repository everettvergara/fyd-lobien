<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Models\InstalledModule;
use App\Models\User;
use App\Modules\Careers\Models\CareerApplication;
use App\Modules\Careers\Models\CareerJob;
use App\Modules\PageManager\Models\Page;
use App\Modules\PageManager\Models\PageBlock;
use App\Services\Recaptcha\RecaptchaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class CareersModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->copyCareersModule();
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(base_path('app/Modules/Careers'));

        parent::tearDown();
    }

    public function test_install_creates_tables_and_permissions(): void
    {
        $this->installCareers();

        $this->assertTrue(Schema::hasTable('career_jobs'));
        $this->assertFalse(Schema::hasTable('career_page_attachments'));
        $this->assertTrue(Schema::hasTable('career_applications'));
        $this->assertDatabaseHas('permissions', ['name' => 'careers.jobs.view']);
        $this->assertDatabaseHas('permissions', ['name' => 'careers.applications.view']);
        $this->assertDatabaseHas('career_jobs', ['slug' => 'senior-web-developer']);
    }

    public function test_public_api_returns_open_jobs(): void
    {
        $this->installCareers();

        $response = $this->getJson('/api/careers/jobs');

        $response->assertOk()
            ->assertJsonPath('jobs.0.slug', 'senior-web-developer');
    }

    public function test_public_apply_stores_application_with_pdf(): void
    {
        $this->installCareers();

        Storage::fake('local');

        $this->mock(RecaptchaService::class, function ($mock) {
            $mock->shouldReceive('enabled')->andReturn(false);
        });

        $response = $this->post('/api/careers/jobs/senior-web-developer/apply', [
            'name' => 'Jane Applicant',
            'email' => 'jane@example.com',
            'contact_number' => '+639171234567',
            'remarks' => 'Available immediately',
            'resume' => UploadedFile::fake()->create('resume.pdf', 100, 'application/pdf'),
        ], ['Accept' => 'application/json']);

        $response->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('career_applications', [
            'email' => 'jane@example.com',
            'name' => 'Jane Applicant',
        ]);

        $application = CareerApplication::first();
        Storage::disk('local')->assertExists($application->resume_path);
    }

    public function test_public_apply_rejects_non_pdf(): void
    {
        $this->installCareers();

        $this->mock(RecaptchaService::class, function ($mock) {
            $mock->shouldReceive('enabled')->andReturn(false);
        });

        $response = $this->post('/api/careers/jobs/senior-web-developer/apply', [
            'name' => 'Jane Applicant',
            'email' => 'jane@example.com',
            'contact_number' => '+639171234567',
            'resume' => UploadedFile::fake()->create('resume.docx', 100, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
        ], ['Accept' => 'application/json']);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['resume']);
    }

    public function test_page_with_careers_listing_block_renders(): void
    {
        $this->installCareers();

        $page = Page::updateOrCreate(
            ['path' => '/careers'],
            [
                'slug' => 'careers',
                'title' => 'Careers',
                'status' => ContentStatus::Published,
                'published_at' => now(),
            ],
        );

        PageBlock::updateOrCreate(
            ['page_id' => $page->id, 'region_key' => 'main', 'block_type' => 'careers-listing'],
            ['sort_order' => 0, 'config' => []],
        );

        $this->get('/careers')
            ->assertOk()
            ->assertInertia(fn ($inertia) => $inertia
                ->component('Page/Show')
                ->where('regions.main.0.type', 'careers-listing'));
    }

    public function test_admin_can_filter_applications_by_job(): void
    {
        $this->installCareers();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $job = CareerJob::where('slug', 'senior-web-developer')->first();

        Storage::fake('local');

        CareerApplication::create([
            'career_job_id' => $job->id,
            'name' => 'Test Applicant',
            'email' => 'test@example.com',
            'contact_number' => '123',
            'resume_path' => 'career-applications/test.pdf',
            'resume_original_filename' => 'test.pdf',
        ]);

        $response = $this->actingAs($admin)->get('/admin/career-applications?job='.$job->id);

        $response->assertOk()
            ->assertSee('Test Applicant', false);
    }

    public function test_admin_can_download_resume(): void
    {
        $this->installCareers();

        Storage::fake('local');

        $admin = User::where('email', 'admin@fyd.local')->first();
        $job = CareerJob::where('slug', 'senior-web-developer')->first();

        Storage::disk('local')->put('career-applications/sample.pdf', 'pdf-content');

        $application = CareerApplication::create([
            'career_job_id' => $job->id,
            'name' => 'Test Applicant',
            'email' => 'test@example.com',
            'contact_number' => '123',
            'resume_path' => 'career-applications/sample.pdf',
            'resume_original_filename' => 'sample.pdf',
        ]);

        $response = $this->actingAs($admin)->get('/admin/career-applications/'.$application->id.'/resume');

        $response->assertOk();
    }

    public function test_job_listings_link_to_filtered_applications(): void
    {
        $this->installCareers();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $job = CareerJob::where('slug', 'senior-web-developer')->first();

        $response = $this->actingAs($admin)->get('/admin/career-jobs');

        $response->assertOk()
            ->assertSee(route('admin.career-applications.index', ['job' => $job->id]), false);
    }

    protected function installCareers(): void
    {
        if (! InstalledModule::where('name', 'Careers')->where('status', InstalledModule::STATUS_INSTALLED)->exists()) {
            $this->seed();
            Artisan::call('module:install', ['name' => 'Careers', '--force' => true]);
        }

        $this->registerModuleRoutes();
        $this->registerModuleViews();
    }

    protected function registerModuleViews(): void
    {
        $viewsPath = base_path('app/Modules/Careers/Views');

        if (is_dir($viewsPath)) {
            View::addNamespace('careers', $viewsPath);
        }
    }

    protected function registerModuleRoutes(): void
    {
        $modulesPath = config('modules.path');
        $module = 'Careers';

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

    protected function copyCareersModule(): void
    {
        $source = base_path('contrib/Careers');
        $target = base_path('app/Modules/Careers');

        if (File::isDirectory($target)) {
            File::deleteDirectory($target);
        }

        File::copyDirectory($source, $target);
    }
}
