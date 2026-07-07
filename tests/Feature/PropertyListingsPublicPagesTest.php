<?php

namespace Tests\Feature;

use App\Enums\MenuLocation;
use App\Models\InstalledModule;
use App\Models\User;
use App\Modules\Address\Models\City;
use App\Modules\Address\Models\Province;
use App\Modules\Menus\Models\Menu;
use App\Modules\Menus\Models\MenuItem;
use App\Modules\PageManager\Models\Page;
use App\Modules\PageManager\Models\PageBlock;
use App\Modules\PropertyListings\Models\Listing;
use App\Modules\PropertyListings\Models\ListingUnit;
use App\Modules\PropertyListings\Services\PropertyListingPageGenerationService;
use App\Modules\PropertyListings\Support\ListingPathHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class PropertyListingsPublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->copyPropertyListingsModule();
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(base_path('app/Modules/PropertyListings'));

        parent::tearDown();
    }

    public function test_slug_is_auto_generated_when_creating_listing(): void
    {
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();

        $this->actingAs($admin)->post('/admin/listings', [
            'code' => 'SLUG-001',
            'name' => 'Ayala Tower One',
            'city' => 'Makati City',
            'completion_status' => 'existing',
        ])->assertRedirect();

        $this->assertDatabaseHas('listings', [
            'code' => 'SLUG-001',
            'slug' => 'ayala-tower-one',
            'city' => 'Makati City',
        ]);
    }

    public function test_page_generation_creates_city_and_listing_pages(): void
    {
        $this->installPropertyListings();

        $listing = $this->createPublishedListing([
            'name' => 'Pacific Plaza',
            'slug' => 'pacific-plaza',
            'city' => 'Makati City',
        ]);

        $stats = app(PropertyListingPageGenerationService::class)->syncAll();

        $cityPath = ListingPathHelper::cityPath('makati-city');
        $listingPath = ListingPathHelper::listingPath($listing->fresh());

        $this->assertDatabaseHas('pages', ['path' => $cityPath]);
        $this->assertDatabaseHas('pages', ['path' => $listingPath]);
        $this->assertDatabaseHas('page_blocks', [
            'block_type' => 'property-listing-detail',
        ]);
        $this->assertDatabaseHas('page_blocks', [
            'block_type' => 'property-listings-city',
        ]);
    }

    public function test_public_listing_page_renders_detail_block(): void
    {
        $this->installPropertyListings();

        $listing = $this->createPublishedListing([
            'name' => 'One Ayala',
            'slug' => 'one-ayala',
            'city' => 'Makati City',
        ]);

        app(PropertyListingPageGenerationService::class)->syncListingPage($listing->fresh());

        $path = $listing->fresh()->publicPath();

        $this->get($path)
            ->assertOk()
            ->assertInertia(fn ($inertia) => $inertia
                ->component('Page/Show')
                ->where('regions.main.0.type', 'property-listing-detail')
                ->where('regions.main.0.props.listing.slug', 'one-ayala'));
    }

    public function test_public_api_returns_listing_detail(): void
    {
        $this->installPropertyListings();

        $listing = $this->createPublishedListing([
            'name' => 'API Tower',
            'slug' => 'api-tower',
            'city' => 'Taguig City',
        ]);

        $this->getJson('/api/property-listings/cities/taguig-city/listings/api-tower')
            ->assertOk()
            ->assertJsonPath('listing.slug', 'api-tower')
            ->assertJsonPath('listing.name', 'API Tower');
    }

    public function test_generate_pages_endpoint_returns_progress_payload(): void
    {
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $this->createPublishedListing([
            'name' => 'Progress Tower',
            'slug' => 'progress-tower',
            'city' => 'Quezon City',
        ]);

        $start = $this->actingAs($admin)
            ->postJson('/admin/listings/configuration/generate-pages');

        $start->assertOk()->assertJsonStructure(['batch_id']);
        $batchId = $start->json('batch_id');

        $status = $this->actingAs($admin)
            ->getJson('/admin/listings/configuration/generate-pages/status?batch_id='.$batchId);

        $status->assertOk()
            ->assertJsonPath('status', 'completed');
    }

    public function test_generate_creates_hub_search_pages_and_footer_menu(): void
    {
        $this->installPropertyListings();

        $this->createPublishedListing([
            'name' => 'Hub Tower',
            'slug' => 'hub-tower',
            'city' => 'Makati City',
        ]);

        app(PropertyListingPageGenerationService::class)->syncAll();

        $this->assertDatabaseHas('pages', ['path' => '/properties']);
        $this->assertDatabaseHas('pages', ['path' => '/properties/search']);
        $this->assertDatabaseHas('page_blocks', ['block_type' => 'property-search-banner']);
        $this->assertDatabaseHas('page_blocks', ['block_type' => 'property-listings-cities']);
        $this->assertDatabaseHas('page_blocks', ['block_type' => 'property-search-results']);

        $footer = Menu::query()->where('location', MenuLocation::Footer)->first();
        $this->assertNotNull($footer);

        $parent = MenuItem::query()
            ->where('menu_id', $footer->id)
            ->whereNull('parent_id')
            ->where('url', '/properties')
            ->first();
        $this->assertNotNull($parent);
        $this->assertSame('Properties', $parent->title);

        $this->assertDatabaseHas('menu_items', [
            'menu_id' => $footer->id,
            'parent_id' => $parent->id,
            'url' => '/properties/makati-city',
        ]);
    }

    public function test_clear_public_website_removes_generated_pages_and_menu(): void
    {
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $listing = $this->createPublishedListing([
            'name' => 'Clear Tower',
            'slug' => 'clear-tower',
            'city' => 'Cebu City',
        ]);

        app(PropertyListingPageGenerationService::class)->syncAll();
        $this->assertDatabaseHas('pages', ['path' => '/properties']);

        $this->actingAs($admin)
            ->post('/admin/listings/configuration/clear-pages')
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('pages', ['path' => '/properties']);
        $this->assertDatabaseMissing('pages', ['path' => '/properties/search']);
        $this->assertDatabaseMissing('pages', ['path' => '/properties/cebu-city']);
        $this->assertDatabaseMissing('pages', ['path' => '/properties/cebu-city/clear-tower']);
        $this->assertDatabaseMissing('menu_items', ['url' => '/properties']);
        $this->assertNull($listing->fresh()->public_page_path);
    }

    public function test_search_page_filters_listings_by_name(): void
    {
        $this->installPropertyListings();

        $this->createPublishedListing([
            'name' => 'Pacific Star',
            'slug' => 'pacific-star',
            'city' => 'Makati City',
        ]);
        $this->createPublishedListing([
            'name' => 'Orient Square',
            'slug' => 'orient-square',
            'city' => 'Pasig City',
        ]);

        app(PropertyListingPageGenerationService::class)->syncAll();

        $this->get('/properties/search?name=Pacific')
            ->assertOk()
            ->assertInertia(fn ($inertia) => $inertia
                ->component('Page/Show')
                ->where('regions.main.0.type', 'property-search-results')
                ->where('regions.main.0.props.filters.name', 'Pacific')
                ->has('regions.main.0.props.listings', 1)
                ->where('regions.main.0.props.listings.0.slug', 'pacific-star'));
    }

    public function test_city_page_exposes_city_profile_and_filtered_listings(): void
    {
        $this->installPropertyListings();

        $province = Province::firstOrCreate(['name' => 'Metro Manila'], ['is_active' => true]);
        City::updateOrCreate(
            ['name' => 'Makati City'],
            [
                'province_id' => $province->id,
                'summary' => 'The financial heart of the Philippines.',
                'description' => '<p>Makati hosts the country\'s premier business district.</p>',
                'is_active' => true,
            ],
        );

        $listing = $this->createPublishedListing([
            'name' => 'City Profile Tower',
            'slug' => 'city-profile-tower',
            'city' => 'Makati City',
        ]);
        ListingUnit::create([
            'listing_id' => $listing->id,
            'floor' => '10',
            'unit' => '10A',
            'for_lease' => true,
            'for_sale' => false,
        ]);

        app(PropertyListingPageGenerationService::class)->syncAll();

        $this->get('/properties/makati-city')
            ->assertOk()
            ->assertInertia(fn ($inertia) => $inertia
                ->component('Page/Show')
                ->where('regions.main.0.type', 'property-listings-city')
                ->where('regions.main.0.props.mode', 'city')
                ->where('regions.main.0.props.city.summary', 'The financial heart of the Philippines.')
                ->where('regions.main.0.props.filters.property_type', 'all')
                ->where('regions.main.0.props.filters.intent', 'all')
                ->has('regions.main.0.props.listings', 1)
                ->where('regions.main.0.props.listings.0.for_lease', true)
                ->where('regions.main.0.props.listings.0.url', url('/properties/makati-city/city-profile-tower')));

        $this->get('/properties/makati-city?intent=sale')
            ->assertOk()
            ->assertInertia(fn ($inertia) => $inertia
                ->has('regions.main.0.props.listings', 0));
    }

    public function test_uninstall_removes_property_blocks_from_pages(): void
    {
        $this->installPropertyListings();

        $listing = $this->createPublishedListing([
            'name' => 'Cleanup Tower',
            'slug' => 'cleanup-tower',
            'city' => 'Pasig City',
        ]);

        app(PropertyListingPageGenerationService::class)->syncListingPage($listing->fresh());
        $page = Page::query()->where('path', $listing->fresh()->publicPath())->first();
        $this->assertNotNull($page);

        PageBlock::create([
            'page_id' => $page->id,
            'region_key' => 'main',
            'block_type' => 'property-listing-detail',
            'sort_order' => 0,
            'config' => [],
        ]);

        Artisan::call('module:uninstall', ['name' => 'PropertyListings', '--force' => true]);

        $this->assertDatabaseMissing('page_blocks', [
            'page_id' => $page->id,
            'block_type' => 'property-listing-detail',
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function createPublishedListing(array $overrides = []): Listing
    {
        return Listing::create(array_merge([
            'code' => 'PUB-'.uniqid(),
            'name' => 'Published Tower',
            'slug' => 'published-tower',
            'city' => 'Makati City',
            'completion_status' => 'existing',
            'published_to_public' => true,
        ], $overrides));
    }

    protected function installPropertyListings(): void
    {
        if (! InstalledModule::where('name', 'PropertyListings')->where('status', InstalledModule::STATUS_INSTALLED)->exists()) {
            $this->seed();
            Artisan::call('module:install', ['name' => 'PropertyListings', '--force' => true]);
        }

        $this->registerModuleRoutes();
        $this->registerModuleViews();
    }

    protected function registerModuleViews(): void
    {
        $viewsPath = base_path('app/Modules/PropertyListings/Views');

        if (is_dir($viewsPath)) {
            View::addNamespace('propertylistings', $viewsPath);
        }
    }

    protected function registerModuleRoutes(): void
    {
        $modulesPath = config('modules.path');
        $module = 'PropertyListings';

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

    protected function copyPropertyListingsModule(): void
    {
        $source = base_path('contrib/PropertyListings');
        $target = base_path('app/Modules/PropertyListings');

        if (File::isDirectory($target)) {
            File::deleteDirectory($target);
        }

        File::copyDirectory($source, $target);
    }
}
