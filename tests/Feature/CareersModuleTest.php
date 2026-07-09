<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Models\InstalledModule;
use App\Models\User;
use App\Modules\Careers\Models\CareerApplication;
use App\Modules\Careers\Models\CareerJob;
use App\Modules\Careers\Services\CareerPageSyncService;
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

    public function test_admin_create_job_page_loads(): void
    {
        $this->installCareers();

        $admin = User::where('email', 'admin@fyd.local')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.career-jobs.create'))
            ->assertOk()
            ->assertSee('Create Job Listing', false);
    }

    public function test_install_creates_careers_page_with_listing_block(): void
    {
        $this->installCareers();

        $page = Page::query()->where('path', '/careers')->first();

        $this->assertNotNull($page);
        $this->assertSame('Careers', $page->title);
        $this->assertSame(ContentStatus::Published, $page->status);

        $this->assertDatabaseHas('page_blocks', [
            'page_id' => $page->id,
            'region_key' => 'main',
            'block_type' => 'careers-listing',
        ]);
    }

    public function test_install_does_not_overwrite_existing_careers_page(): void
    {
        $this->seed();

        $page = Page::create([
            'path' => '/careers',
            'slug' => 'careers',
            'title' => 'Custom Careers',
            'status' => ContentStatus::Published,
            'published_at' => now(),
        ]);

        PageBlock::create([
            'page_id' => $page->id,
            'region_key' => 'main',
            'block_type' => 'page-body',
            'sort_order' => 0,
            'config' => [],
        ]);

        Artisan::call('module:install', ['name' => 'Careers', '--force' => true]);

        $page->refresh();

        $this->assertSame('Custom Careers', $page->title);
        $this->assertDatabaseMissing('page_blocks', [
            'page_id' => $page->id,
            'block_type' => 'careers-listing',
        ]);
        $this->assertDatabaseHas('page_blocks', [
            'page_id' => $page->id,
            'block_type' => 'page-body',
        ]);
    }

    public function test_public_api_returns_open_jobs(): void
    {
        $this->installCareers();

        $response = $this->getJson('/api/careers/jobs');

        $response->assertOk()
            ->assertJsonPath('jobs.0.slug', 'senior-web-developer')
            ->assertJsonStructure([
                'jobs',
                'pagination' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    }

    public function test_public_api_returns_all_job_fields(): void
    {
        $this->installCareers();

        $response = $this->getJson('/api/careers/jobs');

        $response->assertOk()
            ->assertJsonStructure([
                'jobs' => [
                    '*' => [
                        'id',
                        'title',
                        'slug',
                        'summary',
                        'description',
                        'requirements',
                        'department',
                        'location',
                        'salary_range',
                        'employment_type',
                        'employment_type_label',
                        'closing_date',
                        'published_at',
                        'sort_order',
                        'picture',
                        'url',
                    ],
                ],
            ]);
    }

    public function test_public_api_paginates_jobs(): void
    {
        $this->installCareers();
        $this->seedExtraJobs(8);

        $response = $this->getJson('/api/careers/jobs?per_page=5&page=2');

        $response->assertOk()
            ->assertJsonPath('pagination.current_page', 2)
            ->assertJsonPath('pagination.per_page', 5)
            ->assertJsonPath('pagination.total', 10)
            ->assertJsonCount(5, 'jobs');
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

        $this->get('/careers')
            ->assertOk()
            ->assertInertia(fn ($inertia) => $inertia
                ->component('Page/Show')
                ->where('regions.main.0.type', 'careers-listing')
                ->has('regions.main.0.props.jobs', 2)
                ->where('regions.main.0.props.jobs.0.slug', 'senior-web-developer')
                ->where('regions.main.0.props.jobs.0.url', url('/careers/senior-web-developer'))
                ->where('regions.main.0.props.pagination.current_page', 1)
                ->where('regions.main.0.props.pagination.total', 2));
    }

    public function test_careers_listing_paginates(): void
    {
        $this->installCareers();
        $this->seedExtraJobs(8);

        $page = Page::query()->where('path', '/careers')->first();
        PageBlock::query()
            ->where('page_id', $page->id)
            ->where('block_type', 'careers-listing')
            ->update(['config' => ['per_page' => 5]]);

        $this->get('/careers?page=2')
            ->assertOk()
            ->assertInertia(fn ($inertia) => $inertia
                ->component('Page/Show')
                ->has('regions.main.0.props.jobs', 5)
                ->where('regions.main.0.props.pagination.current_page', 2)
                ->where('regions.main.0.props.pagination.total', 10));
    }

    public function test_job_listing_links_to_detail_page(): void
    {
        $this->installCareers();

        $job = CareerJob::where('slug', 'senior-web-developer')->first();

        $this->get('/careers/senior-web-developer')
            ->assertOk()
            ->assertInertia(fn ($inertia) => $inertia
                ->component('Careers/Show')
                ->where('job.slug', 'senior-web-developer')
                ->where('job.title', $job->title)
                ->where('job.description', $job->description)
                ->where('job.url', url('/careers/senior-web-developer')));
    }

    public function test_uninstall_removes_managed_careers_page(): void
    {
        $this->installCareers();

        $page = Page::query()->where('path', CareerPageSyncService::INDEX_PATH)->first();
        $this->assertNotNull($page);

        Artisan::call('module:uninstall', ['name' => 'Careers', '--force' => true]);

        $this->assertNull(Page::query()->where('path', CareerPageSyncService::INDEX_PATH)->first());
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

    protected function seedExtraJobs(int $count): void
    {
        for ($i = 1; $i <= $count; $i++) {
            CareerJob::create([
                'slug' => 'extra-job-'.$i,
                'title' => 'Extra Job '.$i,
                'description' => '<p>Description '.$i.'</p>',
                'status' => CareerJob::STATUS_PUBLISHED,
                'published_at' => now(),
                'employment_type' => 'full_time',
                'sort_order' => 100 + $i,
            ]);
        }
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
