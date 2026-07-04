<?php

namespace Tests\Feature;

use App\Framework\MenuRegistry;
use App\Models\InstalledModule;
use App\Models\User;
use App\Modules\PropertyListings\Models\Listing;
use App\Modules\PropertyListings\Models\ListingLookup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class PropertyListingsModuleTest extends TestCase
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

    public function test_install_creates_tables_permissions_and_seed_data(): void
    {
        $this->installPropertyListings();

        $this->assertTrue(Schema::hasTable('listing_lookups'));
        $this->assertTrue(Schema::hasTable('listings'));
        $this->assertTrue(Schema::hasTable('listing_units'));
        $this->assertDatabaseHas('permissions', ['name' => 'listings.view']);
        $this->assertDatabaseHas('permissions', ['name' => 'listings.lookups.view']);
        $this->assertDatabaseHas('listing_lookups', ['group' => 'completion_status', 'value' => 'existing']);
        $this->assertDatabaseHas('listings', ['code' => 'DEMO-001']);
    }

    public function test_admin_can_view_listings_index(): void
    {
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();

        $this->actingAs($admin)
            ->get('/admin/listings')
            ->assertOk()
            ->assertSee('DEMO-001', false);
    }

    public function test_admin_can_view_lookup_hub(): void
    {
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();

        $this->actingAs($admin)
            ->get('/admin/listing-lookups')
            ->assertOk()
            ->assertSee('property_type', false)
            ->assertSee('Completion Status', false);
    }

    public function test_admin_can_create_listing(): void
    {
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();

        $response = $this->actingAs($admin)->post('/admin/listings', [
            'code' => 'TEST-100',
            'name' => 'Test Tower',
            'province' => 'Metro Manila',
            'city' => 'Makati City',
            'completion_status' => 'existing',
            'spec' => [
                'developer' => 'Test Developer',
                'grade' => 'a',
            ],
            'units' => [
                [
                    'floor' => '10F',
                    'unit' => '10-A',
                    'area_size' => '100',
                    'rental' => '900',
                    'handover_condition' => 'bare-shell',
                    'availability' => 'vacant',
                    'property_type' => 'commercial-office',
                    'for_lease' => '1',
                    'for_sale' => '0',
                ],
            ],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('listings', [
            'code' => 'TEST-100',
            'name' => 'Test Tower',
        ]);

        $this->assertDatabaseHas('listing_units', [
            'floor' => '10F',
            'unit' => '10-A',
        ]);
    }

    public function test_admin_can_compare_listings(): void
    {
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $demo = Listing::where('code', 'DEMO-001')->first();

        $second = Listing::create([
            'code' => 'TEST-200',
            'name' => 'Second Tower',
            'province' => 'Metro Manila',
            'city' => 'Taguig City',
            'completion_status' => 'pipeline',
        ]);

        $this->actingAs($admin)
            ->get('/admin/listings/compare?ids='.$demo->id.','.$second->id)
            ->assertOk()
            ->assertSee('DEMO-001', false)
            ->assertSee('TEST-200', false);
    }

    public function test_csv_import_preview_and_commit(): void
    {
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();

        $csv = implode("\n", [
            'code,name,province,city,completion_status,floor,unit,area_size,rental,handover_condition,availability,property_type,for_lease,for_sale',
            'IMP-001,Import Tower,Metro Manila,Makati City,existing,5F,5-A,150,800,bare-shell,vacant,commercial-office,1,0',
        ]);

        $file = UploadedFile::fake()->createWithContent('listings.csv', $csv);

        $preview = $this->actingAs($admin)
            ->post('/admin/listings/import/preview', ['file' => $file]);

        $preview->assertOk()
            ->assertSee('Import Tower', false);

        $importKey = session('listing_import.path');
        $this->assertNotEmpty($importKey);

        $commit = $this->actingAs($admin)
            ->post('/admin/listings/import/commit', ['import_key' => $importKey]);

        $commit->assertRedirect('/admin/listings');

        $this->assertDatabaseHas('listings', ['code' => 'IMP-001', 'name' => 'Import Tower']);
    }

    public function test_lookup_group_page_lists_values(): void
    {
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();

        $this->actingAs($admin)
            ->get('/admin/listing-lookups/completion-status')
            ->assertOk()
            ->assertSee('Existing', false)
            ->assertSee('Pipeline', false);
    }

    public function test_lookup_create_and_edit_pages_render(): void
    {
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $lookup = ListingLookup::where('group', 'completion_status')->where('value', 'existing')->first();

        $this->actingAs($admin)
            ->get('/admin/listing-lookups/completion-status/create')
            ->assertOk()
            ->assertSee('Add Completion Status Value', false)
            ->assertSee(route('admin.listing-lookups.store', ['group' => 'completion-status']), false);

        $this->actingAs($admin)
            ->get('/admin/listing-lookups/completion-status/'.$lookup->id.'/edit')
            ->assertOk()
            ->assertSee('Edit Lookup Value', false)
            ->assertSee(route('admin.listing-lookups.update', ['group' => 'completion-status', 'listing_lookup' => $lookup->id]), false);
    }

    public function test_admin_can_store_and_update_lookup(): void
    {
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();

        $this->actingAs($admin)
            ->post('/admin/listing-lookups/completion-status', [
                'group' => 'completion_status',
                'value' => 'test-status',
                'label' => 'Test Status',
                'sort_order' => 99,
                'is_active' => '1',
            ])
            ->assertRedirect('/admin/listing-lookups/completion-status');

        $this->assertDatabaseHas('listing_lookups', [
            'group' => 'completion_status',
            'value' => 'test-status',
            'label' => 'Test Status',
        ]);

        $lookup = ListingLookup::where('value', 'test-status')->first();

        $this->actingAs($admin)
            ->put('/admin/listing-lookups/completion-status/'.$lookup->id, [
                'label' => 'Updated Status',
                'sort_order' => 100,
                'is_active' => '1',
            ])
            ->assertRedirect('/admin/listing-lookups/completion-status');

        $this->assertDatabaseHas('listing_lookups', [
            'id' => $lookup->id,
            'label' => 'Updated Status',
        ]);
    }

    public function test_dropdown_values_menu_icon_renders(): void
    {
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $panels = app(MenuRegistry::class)->panelsFor($admin);

        $dropdownItem = collect($panels['business'])
            ->flatMap(fn (array $section) => $section['items'])
            ->firstWhere('label', 'Dropdown Values');

        $this->assertNotNull($dropdownItem);
        $this->assertSame('bi-menu-button-wide', $dropdownItem['icon']);
        $this->assertSame('bi bi-menu-button-wide-fill', admin_icon($dropdownItem['icon']));
    }

    public function test_listing_form_has_no_units_filter(): void
    {
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();

        $this->actingAs($admin)
            ->get('/admin/listings/create')
            ->assertOk()
            ->assertDontSee('Filter units', false);
    }

    public function test_listing_form_section_grid_and_other_services_label(): void
    {
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();

        $this->actingAs($admin)
            ->get('/admin/listings/create')
            ->assertOk()
            ->assertSee('Other Services', false)
            ->assertSee('listing-relation-tabs', false)
            ->assertSee('listing-tab-units', false)
            ->assertSee('listing-tab-fees', false)
            ->assertSee('listing-tab-assets', false);
    }

    public function test_listings_index_has_collapsible_filters(): void
    {
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();

        $this->actingAs($admin)
            ->get('/admin/listings')
            ->assertOk()
            ->assertSee('data-listings-filters', false)
            ->assertSee('data-listings-filters-toggle', false)
            ->assertSee('data-listings-filters-panel', false);
    }

    public function test_listings_edit_has_remarks_toggle_and_dark_panel(): void
    {
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $listing = Listing::where('code', 'DEMO-001')->first();

        $this->actingAs($admin)
            ->get('/admin/listings/'.$listing->id.'/edit')
            ->assertOk()
            ->assertSee('data-listing-remarks-shell', false)
            ->assertSee('data-listing-remarks-collapse', false)
            ->assertSee('listing-remarks-panel', false);
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
