<?php

namespace Tests\Feature;

use App\Framework\MenuRegistry;
use App\Models\InstalledModule;
use App\Models\Media;
use App\Models\MediaVariant;
use App\Models\User;
use App\Modules\PropertyListings\Models\Listing;
use App\Modules\PropertyListings\Models\ListingAsset;
use App\Modules\PropertyListings\Models\ListingLookup;
use App\Modules\PropertyListings\Models\ListingFee;
use App\Modules\PropertyListings\Models\ListingRemark;
use App\Modules\PropertyListings\Models\ListingUnit;
use App\Modules\PropertyListings\Services\ListingAssetImageProcessor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
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
    }

    public function test_uninstall_drops_property_listing_tables(): void
    {
        $this->installPropertyListings();

        foreach ($this->propertyListingTables() as $table) {
            $this->assertTrue(Schema::hasTable($table), "Expected {$table} to exist before uninstall.");
        }

        Artisan::call('module:uninstall', ['name' => 'PropertyListings', '--force' => true]);

        foreach ($this->propertyListingTables() as $table) {
            $this->assertFalse(Schema::hasTable($table), "Expected {$table} to be dropped during uninstall.");
        }

        foreach ($this->propertyListingMigrations() as $migration) {
            $this->assertDatabaseMissing('migrations', ['migration' => $migration]);
        }

        Artisan::call('module:install', ['name' => 'PropertyListings', '--force' => true]);

        foreach ($this->propertyListingTables() as $table) {
            $this->assertTrue(Schema::hasTable($table), "Expected {$table} to be recreated after reinstall.");
        }
    }

    public function test_admin_can_view_listings_index(): void
    {
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $listing = $this->createSampleListing();

        $this->actingAs($admin)
            ->get('/admin/listings')
            ->assertOk()
            ->assertSee($listing->code, false);
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
                'density_ratio' => '1:450',
                'floor_efficiency' => 'high efficiency',
            ],
            'building_service' => [
                'no_of_lifts_passenger' => '8 passenger lifts',
                'no_of_lifts_service' => '2 service lifts',
                'backup_power' => '100% backup',
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

        $this->assertDatabaseHas('listing_specs', [
            'developer' => 'Test Developer',
            'density_ratio' => '1:450',
            'floor_efficiency' => 'high efficiency',
        ]);

        $this->assertDatabaseHas('listing_building_services', [
            'no_of_lifts_passenger' => '8 passenger lifts',
            'no_of_lifts_service' => '2 service lifts',
            'backup_power' => '100% backup',
        ]);
    }

    public function test_admin_can_create_listing_without_province_and_city(): void
    {
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();

        $this->actingAs($admin)->post('/admin/listings', [
            'code' => 'TEST-NO-LOCATION',
            'name' => 'No Location Tower',
            'province' => '',
            'city' => '',
        ])->assertRedirect();

        $this->assertDatabaseHas('listings', [
            'code' => 'TEST-NO-LOCATION',
            'name' => 'No Location Tower',
            'province' => null,
            'city' => null,
        ]);
    }

    public function test_deleting_listing_permanently_deletes_asset_media_and_variants(): void
    {
        Storage::fake('public');
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $listing = $this->createSampleListing();

        Storage::disk('public')->put('property-listings/original.pdf', 'original-content');
        Storage::disk('public')->put('property-listings/variants/thumb.webp', 'variant-content');

        $media = Media::create([
            'filename' => 'original.pdf',
            'original_filename' => 'original.pdf',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size' => 16,
            'disk' => 'public',
            'path' => 'property-listings/original.pdf',
            'uploaded_by' => $admin->id,
        ]);

        MediaVariant::create([
            'media_id' => $media->id,
            'variant' => 'thumbnail',
            'disk' => 'public',
            'storage_provider' => 'local',
            'path' => 'property-listings/variants/thumb.webp',
            'mime_type' => 'image/webp',
            'extension' => 'webp',
            'size' => 15,
        ]);

        ListingAsset::create([
            'listing_id' => $listing->id,
            'asset_type' => 'flyers',
            'media_id' => $media->id,
            'sort_order' => 0,
        ]);

        $this->actingAs($admin)
            ->delete('/admin/listings/'.$listing->id)
            ->assertRedirect('/admin/listings')
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('listings', ['id' => $listing->id]);
        $this->assertDatabaseMissing('listing_assets', ['listing_id' => $listing->id]);
        $this->assertDatabaseMissing('media', ['id' => $media->id]);
        $this->assertDatabaseMissing('media_variants', ['media_id' => $media->id]);
        Storage::disk('public')->assertMissing('property-listings/original.pdf');
        Storage::disk('public')->assertMissing('property-listings/variants/thumb.webp');
    }

    public function test_admin_can_compare_listings(): void
    {
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $first = $this->createSampleListing();

        ListingUnit::create([
            'listing_id' => $first->id,
            'floor' => '10F',
            'unit' => '10-A',
            'area_size' => 150,
            'rental' => 800,
            'availability' => 'vacant',
            'property_type' => 'commercial-office',
            'for_lease' => true,
        ]);

        ListingFee::create([
            'listing_id' => $first->id,
            'fee_type' => 'parking-fee',
            'fee' => 2500.00,
            'sort_order' => 0,
        ]);

        $second = Listing::create([
            'code' => 'TEST-200',
            'name' => 'Second Tower',
            'province' => 'Metro Manila',
            'city' => 'Taguig City',
            'completion_status' => 'pipeline',
        ]);

        $this->actingAs($admin)
            ->get('/admin/listings/compare?ids='.$first->id.','.$second->id)
            ->assertOk()
            ->assertSee($first->code, false)
            ->assertSee($second->code, false)
            ->assertSee('listing-compare-print', false)
            ->assertSee('data-listing-compare-images="building"', false)
            ->assertSee('data-listing-compare-images="floor-plan"', false)
            ->assertSee('data-listing-compare-location', false)
            ->assertSee('data-listing-compare-units', false)
            ->assertSee('data-listing-compare-unit-filter', false)
            ->assertSee('data-listing-compare-unit-row', false)
            ->assertSee('data-unit-area=', false)
            ->assertSee('listing-compare-units-table', false)
            ->assertSee('data-listing-compare-fees', false)
            ->assertSee('listing-compare-fees-table', false)
            ->assertSee('listing-compare-listing-col', false)
            ->assertSee('listing-compare-table', false)
            ->assertSee('listing-compare-responsive', false)
            ->assertSee('Parking Fee', false)
            ->assertSee('₱2,500.00', false)
            ->assertSee('150.00', false)
            ->assertSee('₱800.00', false)
            ->assertSee('10F', false)
            ->assertSee('10-A', false)
            ->assertSee('listingCompareImagePreviewModal', false)
            ->assertSee('data-listing-compare-disclaimer', false)
            ->assertSee('prepared in good faith for the information of potential lessees', false)
            ->assertSee('does not form part of any offer or contract', false)
            ->assertDontSee('>Counts</th>', false);
    }

    public function test_admin_can_preview_listing_brochures(): void
    {
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $listing = $this->createSampleListingWithUnits();

        ListingFee::create([
            'listing_id' => $listing->id,
            'fee_type' => 'dues-cusa',
            'fee' => 180.00,
            'sort_order' => 0,
        ]);

        $siteLogo = Media::create([
            'filename' => 'site-logo.png',
            'original_filename' => 'site-logo.png',
            'mime_type' => 'image/png',
            'extension' => 'png',
            'size' => 128,
            'disk' => 'public',
            'path' => 'branding/site-logo.png',
            'uploaded_by' => $admin->id,
        ]);

        \App\Models\Setting::set('general', 'site_logo_id', (string) $siteLogo->id);

        $types = [
            'interior',
            'property-photos',
            'floor-plan',
            'floors-units',
            'property-information',
            'all',
        ];

        foreach ($types as $type) {
            $this->actingAs($admin)
                ->get('/admin/listings/'.$listing->id.'/brochures/'.$type)
                ->assertOk()
                ->assertSee('listing-brochure-document', false)
                ->assertSee('listing-brochure-header', false)
                ->assertSee('listing-brochure-footer', false)
                ->assertSee(strtoupper($listing->name), false)
                ->assertSee('window.print()', false)
                ->assertSee('brochure-hexagon-frame.png', false)
                ->assertSee('data-brochure-site-logo', false)
                ->assertSee('branding/site-logo.png', false)
                ->assertDontSee('data-brochure-logo-fallback', false);
        }

        $this->actingAs($admin)
            ->get('/admin/listings/'.$listing->id.'/brochures/floors-units')
            ->assertSee('Available Floors / Units', false)
            ->assertSee('Other Rates', false)
            ->assertSee('10F', false);

        $this->actingAs($admin)
            ->get('/admin/listings/'.$listing->id.'/brochures/property-information')
            ->assertSee('Property Information', false)
            ->assertSee('Developer', false);

        $this->actingAs($admin)
            ->get('/admin/listings/'.$listing->id.'/brochures/invalid-type')
            ->assertNotFound();

        $this->actingAs($admin)
            ->get('/admin/listings')
            ->assertOk()
            ->assertSee('listing-brochure-shortcuts', false)
            ->assertSee('bi-lamp', false)
            ->assertSee('bi-building', false);
    }

    public function test_csv_import_preview_and_commit(): void
    {
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();

        $csv = implode("\n", [
            'code,name,province,city,brgy,address,office_rental_rate,total_area_size,unit_market_size,retail_market_rate,completion_status,published_to_public,developer,grade,completion_year,completion_qtr,no_of_floors,no_of_basement,density_ratio,parking_allocation,floor_to_ceiling_height,gross_leasable_area,typical_floor_area,typical_retail_floor_area,floor_efficiency,operating_hours,ac_system,no_of_lifts_passenger,no_of_lifts_service,telco,backup_power,peza_accreditation,sustainability,other_info_visible',
            'IMP-001,Import Tower,Metro Manila,Makati,Legazpi Village,Ayala Avenue,900,10000,150,1200,existing,1,Import Developer,a,2026,Q1,30,3,1:8,1:100,2.7,9000,1000,200,85% efficient,24/7,VRF,8 passenger lifts,2 service lifts,Globe,100% backup,yes,LEED,1',
        ]);

        $file = UploadedFile::fake()->createWithContent('listings.csv', $csv);

        $preview = $this->actingAs($admin)
            ->post('/admin/property-uploaders/header/import/preview', ['file' => $file]);

        $preview->assertOk()
            ->assertSee('Import Tower', false);

        $importKey = session('listing_import.path');
        $this->assertNotEmpty($importKey);

        $commit = $this->actingAs($admin)
            ->post('/admin/property-uploaders/header/import/commit', ['import_key' => $importKey]);

        $commit->assertRedirect('/admin/property-uploaders');

        $this->assertDatabaseHas('listings', ['code' => 'IMP-001', 'name' => 'Import Tower']);
        $this->assertDatabaseHas('listing_specs', ['density_ratio' => '1:8', 'floor_efficiency' => '85% efficient']);
        $this->assertDatabaseHas('listing_building_services', [
            'no_of_lifts_passenger' => '8 passenger lifts',
            'no_of_lifts_service' => '2 service lifts',
            'backup_power' => '100% backup',
        ]);
        $this->assertDatabaseMissing('listing_units', ['floor' => '5F', 'unit' => '5-A']);

        $export = $this->actingAs($admin)->get('/admin/property-uploaders/header/export');
        $export->assertOk();
        $exportContent = $export->streamedContent();
        $this->assertStringContainsString('1:8', $exportContent);
        $this->assertStringContainsString('85% efficient', $exportContent);
        $this->assertStringContainsString('8 passenger lifts', $exportContent);
        $this->assertStringContainsString('2 service lifts', $exportContent);
        $this->assertStringContainsString('100% backup', $exportContent);
    }

    public function test_header_csv_import_allows_blank_province_and_city(): void
    {
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $file = UploadedFile::fake()->createWithContent('header-no-location.csv', $this->headerCsv([
            'code' => 'CSV-NO-LOCATION',
            'name' => 'CSV No Location Tower',
            'province' => '',
            'city' => '',
        ]));

        $this->actingAs($admin)
            ->post('/admin/property-uploaders/header/import/preview', ['file' => $file])
            ->assertOk()
            ->assertSee('CSV No Location Tower', false)
            ->assertSee('Confirm Import', false);

        $importKey = session('listing_import.path');
        $this->assertNotEmpty($importKey);

        $this->actingAs($admin)
            ->post('/admin/property-uploaders/header/import/commit', ['import_key' => $importKey])
            ->assertRedirect('/admin/property-uploaders');

        $this->assertDatabaseHas('listings', [
            'code' => 'CSV-NO-LOCATION',
            'name' => 'CSV No Location Tower',
            'province' => null,
            'city' => null,
        ]);
    }

    public function test_header_csv_preview_rejects_unknown_province_and_city(): void
    {
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();

        $unknownProvince = UploadedFile::fake()->createWithContent('header-invalid-province.csv', $this->headerCsv([
            'code' => 'BAD-PROVINCE',
            'province' => 'Nowhere Province',
        ]));

        $this->actingAs($admin)
            ->post('/admin/property-uploaders/header/import/preview', ['file' => $unknownProvince])
            ->assertOk()
            ->assertSee('Province &quot;Nowhere Province&quot; was not found in the province master file.', false)
            ->assertSee('table-danger text-danger fw-semibold', false)
            ->assertSee('aria-label="Field has errors"', false)
            ->assertDontSee('small text-danger fw-normal mt-1', false)
            ->assertDontSee('Confirm Import', false);

        $this->assertDatabaseMissing('listings', ['code' => 'BAD-PROVINCE']);

        $unknownCity = UploadedFile::fake()->createWithContent('header-invalid-city.csv', $this->headerCsv([
            'code' => 'BAD-CITY',
            'province' => 'Metro Manila',
            'city' => 'Unknown City',
        ]));

        $this->actingAs($admin)
            ->post('/admin/property-uploaders/header/import/preview', ['file' => $unknownCity])
            ->assertOk()
            ->assertSee('City &quot;Unknown City&quot; was not found under province &quot;Metro Manila&quot; in the city master file.', false)
            ->assertSee('table-danger text-danger fw-semibold', false)
            ->assertSee('aria-label="Field has errors"', false)
            ->assertDontSee('small text-danger fw-normal mt-1', false)
            ->assertDontSee('Confirm Import', false);

        $this->assertDatabaseMissing('listings', ['code' => 'BAD-CITY']);
    }

    public function test_property_uploaders_page_hosts_csv_and_asset_actions(): void
    {
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();

        $this->actingAs($admin)
            ->get('/admin/listings')
            ->assertOk()
            ->assertDontSee('Download CSV', false)
            ->assertDontSee('Upload CSV', false)
            ->assertDontSee('Batch Assets', false);

        $this->actingAs($admin)
            ->get('/admin/property-uploaders')
            ->assertOk()
            ->assertSee('Property Header', false)
            ->assertSee('Property Units', false)
            ->assertSee('Property Fees', false)
            ->assertSee('Assets Uploader', false);
    }

    public function test_csv_templates_are_split_by_type(): void
    {
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();

        $header = $this->actingAs($admin)->get('/admin/property-uploaders/header/template');
        $header->assertOk();
        $this->assertStringContainsString('developer', $header->streamedContent());
        $this->assertStringNotContainsString('fee_type', $header->streamedContent());
        $this->assertStringNotContainsString('floor,unit', $header->streamedContent());

        $units = $this->actingAs($admin)->get('/admin/property-uploaders/units/template');
        $units->assertOk();
        $this->assertStringContainsString('code,floor,unit', $units->streamedContent());

        $fees = $this->actingAs($admin)->get('/admin/property-uploaders/fees/template');
        $fees->assertOk();
        $this->assertStringContainsString('code,fee_type,fee,sort_order', $fees->streamedContent());
    }

    public function test_units_csv_import_inserts_and_updates_by_code_floor_unit(): void
    {
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $listing = $this->createSampleListing();

        $csv = implode("\n", [
            'code,floor,unit,area_size,rental,handover_condition,availability,bedrooms,selling_price,property_type,for_lease,for_sale,last_remarks,sort_order',
            "{$listing->code},5F,5-A,150,800,bare-shell,vacant,,0,commercial-office,1,0,Initial,0",
        ]);

        $file = UploadedFile::fake()->createWithContent('units.csv', $csv);
        $this->actingAs($admin)->post('/admin/property-uploaders/units/import/preview', ['file' => $file])->assertOk();
        $importKey = session('listing_import.path');
        $this->actingAs($admin)->post('/admin/property-uploaders/units/import/commit', ['import_key' => $importKey])->assertRedirect('/admin/property-uploaders');

        $this->assertDatabaseHas('listing_units', [
            'listing_id' => $listing->id,
            'floor' => '5F',
            'unit' => '5-A',
            'rental' => 800,
        ]);

        $updateCsv = implode("\n", [
            'code,floor,unit,area_size,rental,handover_condition,availability,bedrooms,selling_price,property_type,for_lease,for_sale,last_remarks,sort_order',
            "{$listing->code},5F,5-A,150,900,bare-shell,vacant,,0,commercial-office,1,0,Updated,0",
        ]);

        $file = UploadedFile::fake()->createWithContent('units-update.csv', $updateCsv);
        $this->actingAs($admin)->post('/admin/property-uploaders/units/import/preview', ['file' => $file])->assertOk();
        $importKey = session('listing_import.path');
        $this->actingAs($admin)->post('/admin/property-uploaders/units/import/commit', ['import_key' => $importKey])->assertRedirect('/admin/property-uploaders');

        $this->assertSame(1, ListingUnit::where('listing_id', $listing->id)->where('floor', '5F')->where('unit', '5-A')->count());
        $this->assertDatabaseHas('listing_units', [
            'listing_id' => $listing->id,
            'floor' => '5F',
            'unit' => '5-A',
            'rental' => 900,
        ]);
    }

    public function test_units_csv_warns_and_ignores_rows_with_unknown_listing_code(): void
    {
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $listing = $this->createSampleListing();

        $csv = implode("\n", [
            'code,floor,unit,area_size,rental,handover_condition,availability,bedrooms,selling_price,property_type,for_lease,for_sale,last_remarks,sort_order',
            "{$listing->code},5F,5-A,150,800,bare-shell,vacant,,0,commercial-office,1,0,Valid row,0",
            "MISSING-LISTING,6F,6-A,180,900,bare-shell,vacant,,0,commercial-office,1,0,Ignored row,1",
        ]);

        $file = UploadedFile::fake()->createWithContent('units-missing-parent.csv', $csv);

        $this->actingAs($admin)
            ->post('/admin/property-uploaders/units/import/preview', ['file' => $file])
            ->assertOk()
            ->assertSee('Listing code MISSING-LISTING was not found', false)
            ->assertSee('aria-label="Field has warnings"', false)
            ->assertSee('Confirm Import', false);

        $importKey = session('listing_import.path');
        $this->assertNotEmpty($importKey);

        $this->actingAs($admin)
            ->post('/admin/property-uploaders/units/import/commit', ['import_key' => $importKey])
            ->assertRedirect('/admin/property-uploaders')
            ->assertSessionHas('warning');

        $this->assertDatabaseHas('listing_units', [
            'listing_id' => $listing->id,
            'floor' => '5F',
            'unit' => '5-A',
        ]);
        $this->assertDatabaseMissing('listing_units', [
            'floor' => '6F',
            'unit' => '6-A',
        ]);
    }

    public function test_fees_csv_import_inserts_and_updates_by_code_fee_type(): void
    {
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $listing = $this->createSampleListing();

        $csv = implode("\n", [
            'code,fee_type,fee,sort_order',
            "{$listing->code},parking-fee,2500,0",
        ]);

        $file = UploadedFile::fake()->createWithContent('fees.csv', $csv);
        $this->actingAs($admin)->post('/admin/property-uploaders/fees/import/preview', ['file' => $file])->assertOk();
        $importKey = session('listing_import.path');
        $this->actingAs($admin)->post('/admin/property-uploaders/fees/import/commit', ['import_key' => $importKey])->assertRedirect('/admin/property-uploaders');

        $this->assertDatabaseHas('listing_fees', [
            'listing_id' => $listing->id,
            'fee_type' => 'parking-fee',
            'fee' => 2500,
        ]);

        $updateCsv = implode("\n", [
            'code,fee_type,fee,sort_order',
            "{$listing->code},parking-fee,3000,0",
        ]);

        $file = UploadedFile::fake()->createWithContent('fees-update.csv', $updateCsv);
        $this->actingAs($admin)->post('/admin/property-uploaders/fees/import/preview', ['file' => $file])->assertOk();
        $importKey = session('listing_import.path');
        $this->actingAs($admin)->post('/admin/property-uploaders/fees/import/commit', ['import_key' => $importKey])->assertRedirect('/admin/property-uploaders');

        $this->assertSame(1, ListingFee::where('listing_id', $listing->id)->where('fee_type', 'parking-fee')->count());
        $this->assertDatabaseHas('listing_fees', [
            'listing_id' => $listing->id,
            'fee_type' => 'parking-fee',
            'fee' => 3000,
        ]);
    }

    public function test_fees_csv_duplicate_detection_uses_code_and_fee_type_combination(): void
    {
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $listing = $this->createSampleListing();

        $csv = implode("\n", [
            'code,fee_type,fee,sort_order',
            "{$listing->code},parking-fee,2500,0",
            "{$listing->code},service-charge,100,1",
        ]);

        $file = UploadedFile::fake()->createWithContent('fees-different-types.csv', $csv);

        $this->actingAs($admin)
            ->post('/admin/property-uploaders/fees/import/preview', ['file' => $file])
            ->assertOk()
            ->assertDontSee('Duplicate property code '.$listing->code, false)
            ->assertDontSee('Duplicate fee upload key', false)
            ->assertSee('Confirm Import', false);
    }

    public function test_fees_csv_warns_duplicate_code_and_fee_type_combination(): void
    {
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $listing = $this->createSampleListing();

        $csv = implode("\n", [
            'code,fee_type,fee,sort_order',
            "{$listing->code},parking-fee,2500,0",
            "{$listing->code},parking-fee,3000,1",
        ]);

        $file = UploadedFile::fake()->createWithContent('fees-same-type.csv', $csv);

        $this->actingAs($admin)
            ->post('/admin/property-uploaders/fees/import/preview', ['file' => $file])
            ->assertOk()
            ->assertSee('Duplicate fee upload key '.$listing->code.' + parking-fee', false)
            ->assertSee('aria-label="Field has warnings"', false)
            ->assertSee('Confirm Import', false);

        $importKey = session('listing_import.path');
        $this->assertNotEmpty($importKey);

        $this->actingAs($admin)
            ->post('/admin/property-uploaders/fees/import/commit', ['import_key' => $importKey])
            ->assertRedirect('/admin/property-uploaders')
            ->assertSessionHas('warning');

        $this->assertSame(1, ListingFee::where('listing_id', $listing->id)->where('fee_type', 'parking-fee')->count());
        $this->assertDatabaseHas('listing_fees', [
            'listing_id' => $listing->id,
            'fee_type' => 'parking-fee',
            'fee' => 3000,
        ]);
    }

    public function test_fees_csv_warns_unknown_dropdown_codes_and_imports_them_as_null(): void
    {
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $listing = $this->createSampleListing();

        $csv = implode("\n", [
            'code,fee_type,fee,sort_order',
            "{$listing->code},association-dues,500,0",
        ]);

        $file = UploadedFile::fake()->createWithContent('fees-invalid.csv', $csv);

        $this->actingAs($admin)
            ->post('/admin/property-uploaders/fees/import/preview', ['file' => $file])
            ->assertOk()
            ->assertSee('Warnings:', false)
            ->assertSee('association-dues', false)
            ->assertSee('table-warning text-warning-emphasis fw-semibold', false)
            ->assertSee('aria-label="Field has warnings"', false)
            ->assertDontSee('small text-danger fw-normal mt-1', false)
            ->assertSee('Confirm Import', false);

        $importKey = session('listing_import.path');
        $this->assertNotEmpty($importKey);

        $this->actingAs($admin)
            ->post('/admin/property-uploaders/fees/import/commit', ['import_key' => $importKey])
            ->assertRedirect('/admin/property-uploaders')
            ->assertSessionHas('warning');

        $this->assertDatabaseHas('listing_fees', [
            'listing_id' => $listing->id,
            'fee_type' => null,
            'fee' => 500,
        ]);
    }

    public function test_header_csv_warns_unknown_dropdown_codes_and_imports_them_as_null(): void
    {
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();

        $file = UploadedFile::fake()->createWithContent('header-warning-dropdowns.csv', $this->headerCsv([
            'code' => 'WARN-DROPDOWN',
            'completion_status' => 'future-built',
            'grade' => 'ultra-prime',
            'peza_accreditation' => 'maybe',
        ]));

        $this->actingAs($admin)
            ->post('/admin/property-uploaders/header/import/preview', ['file' => $file])
            ->assertOk()
            ->assertSee('Warnings:', false)
            ->assertSee('future-built', false)
            ->assertSee('ultra-prime', false)
            ->assertSee('maybe', false)
            ->assertSee('table-warning text-warning-emphasis fw-semibold', false)
            ->assertSee('aria-label="Field has warnings"', false)
            ->assertSee('Confirm Import', false);

        $importKey = session('listing_import.path');
        $this->assertNotEmpty($importKey);

        $this->actingAs($admin)
            ->post('/admin/property-uploaders/header/import/commit', ['import_key' => $importKey])
            ->assertRedirect('/admin/property-uploaders')
            ->assertSessionHas('warning');

        $listing = Listing::where('code', 'WARN-DROPDOWN')->firstOrFail();

        $this->assertNull($listing->completion_status);
        $this->assertDatabaseHas('listing_specs', [
            'listing_id' => $listing->id,
            'grade' => null,
        ]);
        $this->assertDatabaseHas('listing_other_infos', [
            'listing_id' => $listing->id,
            'peza_accreditation' => null,
        ]);
    }

    public function test_units_csv_warns_unknown_dropdown_codes_and_imports_them_as_null(): void
    {
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $listing = $this->createSampleListing();

        $csv = implode("\n", [
            'code,floor,unit,area_size,rental,handover_condition,availability,bedrooms,selling_price,property_type,for_lease,for_sale,last_remarks,sort_order',
            "{$listing->code},5F,5-A,150,800,bare-shell,vacant,,0,unknown-property-type,1,0,Initial,0",
        ]);

        $file = UploadedFile::fake()->createWithContent('units-invalid-dropdown.csv', $csv);

        $this->actingAs($admin)
            ->post('/admin/property-uploaders/units/import/preview', ['file' => $file])
            ->assertOk()
            ->assertSee('Warnings:', false)
            ->assertSee('unknown-property-type', false)
            ->assertSee('table-warning text-warning-emphasis fw-semibold', false)
            ->assertSee('aria-label="Field has warnings"', false)
            ->assertDontSee('small text-danger fw-normal mt-1', false)
            ->assertSee('Confirm Import', false);

        $importKey = session('listing_import.path');
        $this->assertNotEmpty($importKey);

        $this->actingAs($admin)
            ->post('/admin/property-uploaders/units/import/commit', ['import_key' => $importKey])
            ->assertRedirect('/admin/property-uploaders')
            ->assertSessionHas('warning');

        $this->assertDatabaseHas('listing_units', [
            'listing_id' => $listing->id,
            'floor' => '5F',
            'unit' => '5-A',
            'property_type' => null,
        ]);
    }

    public function test_assets_uploader_uses_selected_type_and_filename_code_prefix(): void
    {
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $listing = $this->createSampleListing();

        $this->actingAs($admin)
            ->post('/admin/property-uploaders/assets/preview', [
                'asset_type' => 'flyers',
                'files' => [
                    UploadedFile::fake()->create($listing->code.'__marketing-copy.pdf', 64, 'application/pdf'),
                ],
            ])
            ->assertOk()
            ->assertSee($listing->code, false)
            ->assertSee('flyers', false)
            ->assertSee('Attach', false);
    }

    public function test_assets_uploader_can_stage_files_one_at_a_time_and_preview(): void
    {
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $listing = $this->createSampleListing();

        $start = $this->actingAs($admin)
            ->postJson('/admin/property-uploaders/assets/stage/start', [
                'asset_type' => 'flyers',
            ])
            ->assertOk()
            ->assertJsonStructure(['batch_key']);

        $batchKey = $start->json('batch_key');

        $this->actingAs($admin)
            ->postJson('/admin/property-uploaders/assets/stage/file', [
                'batch_key' => $batchKey,
                'file' => UploadedFile::fake()->create($listing->code.'__marketing-copy.pdf', 64, 'application/pdf'),
            ])
            ->assertOk()
            ->assertJson([
                'filename' => $listing->code.'__marketing-copy.pdf',
                'staged' => 1,
            ]);

        $this->actingAs($admin)
            ->post('/admin/property-uploaders/assets/stage/preview', [
                'batch_key' => $batchKey,
            ])
            ->assertOk()
            ->assertSee($listing->code, false)
            ->assertSee('flyers', false)
            ->assertSee('Attach', false)
            ->assertSee('Confirm Upload', false);
    }

    public function test_assets_uploader_reports_staged_preview_validation_statuses(): void
    {
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $listing = $this->createSampleListing();

        $start = $this->actingAs($admin)
            ->postJson('/admin/property-uploaders/assets/stage/start', [
                'asset_type' => 'flyers',
            ])
            ->assertOk();

        $batchKey = $start->json('batch_key');

        foreach ([
            UploadedFile::fake()->create($listing->code.'__marketing-copy.pdf', 64, 'application/pdf'),
            UploadedFile::fake()->create('UNKNOWN-CODE__marketing-copy.pdf', 64, 'application/pdf'),
            UploadedFile::fake()->create('bad-name.pdf', 64, 'application/pdf'),
        ] as $file) {
            $this->actingAs($admin)
                ->postJson('/admin/property-uploaders/assets/stage/file', [
                    'batch_key' => $batchKey,
                    'file' => $file,
                ])
                ->assertOk();
        }

        $this->actingAs($admin)
            ->postJson('/admin/property-uploaders/assets/stage/validate', [
                'batch_key' => $batchKey,
                'index' => 0,
            ])
            ->assertOk()
            ->assertJson([
                'status' => 'valid',
                'message' => 'Attach new asset.',
                'processed' => 1,
                'total' => 3,
                'percent' => 33,
            ]);

        $this->actingAs($admin)
            ->postJson('/admin/property-uploaders/assets/stage/validate', [
                'batch_key' => $batchKey,
                'index' => 1,
            ])
            ->assertOk()
            ->assertJson([
                'status' => 'skip',
                'processed' => 2,
                'total' => 3,
                'percent' => 67,
            ])
            ->assertJsonPath('message', 'Listing code "UNKNOWN-CODE" was not found.');

        $this->actingAs($admin)
            ->postJson('/admin/property-uploaders/assets/stage/validate', [
                'batch_key' => $batchKey,
                'index' => 2,
            ])
            ->assertOk()
            ->assertJson([
                'status' => 'invalid',
                'processed' => 3,
                'total' => 3,
                'percent' => 100,
                'done' => true,
            ])
            ->assertJsonPath('message', 'Filename must match {code}__{whatever_text}.{ext}.');
    }

    public function test_assets_uploader_skips_unknown_listing_codes_and_commits_valid_files(): void
    {
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $listing = $this->createSampleListing();

        $this->actingAs($admin)
            ->post('/admin/property-uploaders/assets/preview', [
                'asset_type' => 'flyers',
                'files' => [
                    UploadedFile::fake()->create($listing->code.'__marketing-copy.pdf', 64, 'application/pdf'),
                    UploadedFile::fake()->create('UNKNOWN-CODE__marketing-copy.pdf', 64, 'application/pdf'),
                ],
            ])
            ->assertOk()
            ->assertSee($listing->code, false)
            ->assertSee('UNKNOWN-CODE', false)
            ->assertSee('These files will be skipped', false)
            ->assertSee('Listing code &quot;UNKNOWN-CODE&quot; was not found.', false)
            ->assertSee('Confirm Upload', false);

        $batchKey = session('listing_batch.key');
        $this->assertNotEmpty($batchKey);

        $this->actingAs($admin)
            ->post('/admin/property-uploaders/assets/commit', [
                'batch_key' => $batchKey,
            ])
            ->assertRedirect('/admin/property-uploaders')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('listing_assets', [
            'listing_id' => $listing->id,
            'asset_type' => 'flyers',
        ]);
        $this->assertSame(1, \App\Modules\PropertyListings\Models\ListingAsset::query()->count());
    }

    public function test_assets_uploader_progress_commit_reports_percent_and_skips_unknown_codes(): void
    {
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $listing = $this->createSampleListing();

        $start = $this->actingAs($admin)
            ->postJson('/admin/property-uploaders/assets/stage/start', [
                'asset_type' => 'flyers',
            ])
            ->assertOk();

        $batchKey = $start->json('batch_key');

        $this->actingAs($admin)
            ->postJson('/admin/property-uploaders/assets/stage/file', [
                'batch_key' => $batchKey,
                'file' => UploadedFile::fake()->create($listing->code.'__marketing-copy.pdf', 64, 'application/pdf'),
            ])
            ->assertOk();

        $this->actingAs($admin)
            ->postJson('/admin/property-uploaders/assets/stage/file', [
                'batch_key' => $batchKey,
                'file' => UploadedFile::fake()->create('UNKNOWN-CODE__marketing-copy.pdf', 64, 'application/pdf'),
            ])
            ->assertOk();

        $this->actingAs($admin)
            ->postJson('/admin/property-uploaders/assets/commit/progress', [
                'batch_key' => $batchKey,
                'index' => 0,
            ])
            ->assertOk()
            ->assertJson([
                'done' => false,
                'processed' => 1,
                'total' => 2,
                'percent' => 50,
                'current' => [
                    'status' => 'attached',
                ],
                'summary' => [
                    'attached' => 1,
                    'skipped' => 0,
                ],
            ]);

        $this->actingAs($admin)
            ->postJson('/admin/property-uploaders/assets/commit/progress', [
                'batch_key' => $batchKey,
                'index' => 1,
            ])
            ->assertOk()
            ->assertJson([
                'done' => true,
                'processed' => 2,
                'total' => 2,
                'percent' => 100,
                'current' => [
                    'status' => 'skipped',
                ],
                'summary' => [
                    'attached' => 1,
                    'skipped' => 1,
                    'failed' => 0,
                ],
            ]);

        $this->assertDatabaseHas('listing_assets', [
            'listing_id' => $listing->id,
            'asset_type' => 'flyers',
        ]);
    }

    public function test_assets_uploader_stage_file_returns_visible_json_errors(): void
    {
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();

        $start = $this->actingAs($admin)
            ->postJson('/admin/property-uploaders/assets/stage/start', [
                'asset_type' => 'flyers',
            ])
            ->assertOk();

        $this->actingAs($admin)
            ->postJson('/admin/property-uploaders/assets/stage/file', [
                'batch_key' => $start->json('batch_key'),
            ])
            ->assertStatus(422)
            ->assertJsonStructure(['message', 'errors']);
    }

    public function test_listing_asset_image_processor_uses_jpeg_quality_75(): void
    {
        $reflection = new \ReflectionClass(ListingAssetImageProcessor::class);

        $this->assertSame(75, $reflection->getReflectionConstant('JPEG_QUALITY')?->getValue());
    }

    public function test_listing_asset_image_processor_converts_processable_images_to_jpeg(): void
    {
        if (! function_exists('imagecreatetruecolor') || ! function_exists('imagepng') || ! function_exists('imagecreatefrompng')) {
            $this->markTestSkipped('GD image functions are not available.');
        }

        $image = imagecreatetruecolor(20, 20);
        $path = tempnam(sys_get_temp_dir(), 'listing-asset-test-');
        imagepng($image, $path);
        imagedestroy($image);

        $file = new UploadedFile($path, 'map-upload.png', 'image/png', null, true);
        $processed = app(ListingAssetImageProcessor::class)->process($file);

        $this->assertSame('jpg', $processed->getClientOriginalExtension());
        $this->assertSame('image/jpeg', $processed->getMimeType());
        $this->assertFileExists($processed->getRealPath());
    }

    public function test_listing_asset_image_processor_skips_images_that_exceed_safe_memory_estimate(): void
    {
        $oldLimit = ini_get('memory_limit');
        ini_set('memory_limit', '128M');

        try {
            $processor = app(ListingAssetImageProcessor::class);
            $method = new \ReflectionMethod($processor, 'canProcessImage');

            $this->assertFalse($method->invoke($processor, 20000, 20000));
        } finally {
            ini_set('memory_limit', $oldLimit ?: '128M');
        }
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
        $listing = $this->createSampleListing();

        $this->actingAs($admin)
            ->get('/admin/listings')
            ->assertOk()
            ->assertSee('listings-filters-toolbar-row', false)
            ->assertSee('listings-filters-toolbar-search', false)
            ->assertSee('data-listings-filters-toggle', false)
            ->assertSee('data-listings-filters-panel', false)
            ->assertSee('admin-list-toolbar', false)
            ->assertSee('data-listing-compare', false)
            ->assertSee('data-listing-comparator-bin', false)
            ->assertSee($listing->code, false);
    }

    public function test_thumbnail_view_renders_cards_with_edit_links(): void
    {
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $listing = $this->createSampleListing();

        $this->actingAs($admin)
            ->get('/admin/listings?view=thumbnails')
            ->assertOk()
            ->assertSee('listing-thumbnail-card', false)
            ->assertSee(route('admin.listings.edit', $listing), false);
    }

    public function test_admin_can_delete_remark(): void
    {
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $listing = $this->createSampleListing();

        $remark = ListingRemark::create([
            'listing_id' => $listing->id,
            'user_id' => $admin->id,
            'comment' => 'Remark to delete',
            'remarked_at' => now(),
        ]);

        $this->actingAs($admin)
            ->delete('/admin/listings/'.$listing->id.'/remarks/'.$remark->id)
            ->assertRedirect('/admin/listings/'.$listing->id.'/edit');

        $this->assertDatabaseMissing('listing_remarks', ['id' => $remark->id]);
    }

    public function test_listings_edit_has_remarks_toggle_and_dark_panel(): void
    {
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $listing = $this->createSampleListing();

        ListingRemark::create([
            'listing_id' => $listing->id,
            'user_id' => $admin->id,
            'comment' => 'Existing remark',
            'remarked_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get('/admin/listings/'.$listing->id.'/edit')
            ->assertOk()
            ->assertSee('data-listing-remarks-panel', false)
            ->assertSee('listing-remarks-panel', false)
            ->assertSee('aria-label="Delete remark"', false);
    }

    public function test_listings_index_shows_unit_summary_and_publish_toggle(): void
    {
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $listing = $this->createSampleListingWithUnits();

        $this->actingAs($admin)
            ->get('/admin/listings')
            ->assertOk()
            ->assertSee('data-listing-published-toggle', false)
            ->assertSee('Commercial - Office Use (Commercial)', false)
            ->assertSee('Commercial - Retail Use (Commercial)', false)
            ->assertSee('Vacant (1)', false)
            ->assertSee('Leased (1)', false)
            ->assertSee('For Lease', false)
            ->assertSee('For Sale', false);

        $this->actingAs($admin)
            ->get('/admin/listings?view=thumbnails')
            ->assertOk()
            ->assertSee('listing-unit-summary', false)
            ->assertSee('Commercial - Office Use (Commercial)', false)
            ->assertSee('Vacant (1)', false)
            ->assertSee('For Lease', false);
    }

    public function test_admin_can_create_listing_with_published_to_public(): void
    {
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();

        $response = $this->actingAs($admin)->post('/admin/listings', [
            'code' => 'PUB-100',
            'name' => 'Published Tower',
            'province' => 'Metro Manila',
            'city' => 'Makati',
            'completion_status' => 'existing',
            'published_to_public' => '1',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('listings', [
            'code' => 'PUB-100',
            'published_to_public' => true,
        ]);
    }

    public function test_admin_can_toggle_published_on_index(): void
    {
        $this->installPropertyListings();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $listing = Listing::create([
            'code' => 'PUB-TOGGLE',
            'name' => 'Toggle Tower',
            'province' => 'Metro Manila',
            'city' => 'Makati City',
            'completion_status' => 'existing',
            'published_to_public' => false,
        ]);

        $this->actingAs($admin)
            ->patchJson('/admin/listings/'.$listing->id.'/published', [
                'published_to_public' => true,
            ])
            ->assertOk()
            ->assertJson(['published_to_public' => true]);

        $this->assertDatabaseHas('listings', [
            'id' => $listing->id,
            'published_to_public' => true,
        ]);
    }

    protected function createSampleListingWithUnits(): Listing
    {
        $listing = $this->createSampleListing();

        ListingUnit::create([
            'listing_id' => $listing->id,
            'floor' => '10F',
            'unit' => '10-A',
            'property_type' => 'commercial-office',
            'availability' => 'vacant',
            'for_lease' => true,
            'for_sale' => false,
            'sort_order' => 0,
        ]);

        ListingUnit::create([
            'listing_id' => $listing->id,
            'floor' => '11F',
            'unit' => '11-B',
            'property_type' => 'commercial-retail',
            'availability' => 'leased',
            'for_lease' => false,
            'for_sale' => true,
            'sort_order' => 1,
        ]);

        return $listing->fresh(['units']);
    }

    protected function createSampleListing(): Listing
    {
        return Listing::create([
            'code' => 'TEST-LIST-'.uniqid(),
            'name' => 'Sample Tower',
            'province' => 'Metro Manila',
            'city' => 'Makati City',
            'completion_status' => 'existing',
        ]);
    }

    /**
     * @return array<int, string>
     */
    protected function propertyListingTables(): array
    {
        return [
            'listing_assets',
            'listing_fees',
            'listing_remarks',
            'listing_units',
            'listing_other_infos',
            'listing_building_services',
            'listing_specs',
            'listings',
            'listing_lookups',
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function propertyListingMigrations(): array
    {
        return [
            '2026_07_04_600001_create_property_listing_tables',
            '2026_07_05_600002_add_published_to_public_to_listings',
            '2026_07_06_600003_ensure_density_ratio_is_string',
            '2026_07_06_600004_allow_nullable_fee_type_on_listing_fees',
            '2026_07_06_600005_allow_nullable_import_conversion_fields',
            '2026_07_06_600006_change_lift_power_and_efficiency_fields_to_strings',
            '2026_07_06_600007_allow_nullable_listing_location',
        ];
    }

    /**
     * @param  array<string, string>  $overrides
     */
    protected function headerCsv(array $overrides = []): string
    {
        $row = array_merge([
            'code' => 'HEADER-001',
            'name' => 'Header Tower',
            'province' => 'Metro Manila',
            'city' => 'Makati',
            'brgy' => 'Legazpi Village',
            'address' => 'Ayala Avenue',
            'office_rental_rate' => '900',
            'total_area_size' => '10000',
            'unit_market_size' => '150',
            'retail_market_rate' => '1200',
            'completion_status' => 'existing',
            'published_to_public' => '1',
            'developer' => 'Header Developer',
            'grade' => 'a',
            'completion_year' => '2026',
            'completion_qtr' => 'Q1',
            'no_of_floors' => '30',
            'no_of_basement' => '3',
            'density_ratio' => '1:8',
            'parking_allocation' => '1:100',
            'floor_to_ceiling_height' => '2.7',
            'gross_leasable_area' => '9000',
            'typical_floor_area' => '1000',
            'typical_retail_floor_area' => '200',
            'floor_efficiency' => '85% efficient',
            'operating_hours' => '24/7',
            'ac_system' => 'VRF',
            'no_of_lifts_passenger' => '8 passenger lifts',
            'no_of_lifts_service' => '2 service lifts',
            'telco' => 'Globe',
            'backup_power' => '100% backup',
            'peza_accreditation' => 'yes',
            'sustainability' => 'LEED',
            'other_info_visible' => '1',
        ], $overrides);

        return implode("\n", [
            implode(',', array_keys($row)),
            implode(',', array_values($row)),
        ]);
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
