<?php

namespace App\Modules\PropertyListings\Services;

use App\Models\User;
use App\Modules\PropertyListings\Models\Listing;
use App\Modules\PropertyListings\Models\ListingAsset;
use App\Modules\PropertyListings\Models\ListingFee;
use App\Modules\PropertyListings\Models\ListingRemark;
use App\Modules\PropertyListings\Models\ListingUnit;
use App\Modules\PropertyListings\Support\ListingLookupGroups;
use App\Modules\PropertyListings\Support\ListingLookupRegistry;
use App\Services\Media\MediaUploadService;
use App\Services\Media\MediaUsageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ListingDemoSeedService
{
    /** @var array<int, string> */
    protected array $assetColors = [
        'building' => '#2563eb',
        'floor-plan' => '#059669',
        'map' => '#d97706',
        'interior' => '#7c3aed',
    ];

    public function __construct(
        protected ListingLookupRegistry $registry,
        protected ListingAssetImageProcessor $imageProcessor,
        protected MediaUploadService $mediaUpload,
        protected MediaUsageService $mediaUsage,
    ) {}

    /** Minimal valid JPEG used when PHP GD is unavailable. */
    private const FALLBACK_JPEG = '/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAn/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCwABmX/9k=';

    /**
     * @return array{seeded: int, image_fallback: bool}
     */
    public function seed(): array
    {
        $admin = User::query()->where('email', 'admin@fyd.local')->first();
        $seeded = 0;
        $imageFallback = ! $this->gdAvailable();

        DB::transaction(function () use ($admin, &$seeded) {
            foreach ($this->demoListingDefinitions() as $definition) {
                $this->seedOne($definition, $admin);
                $seeded++;
            }
        });

        return [
            'seeded' => $seeded,
            'image_fallback' => $imageFallback,
        ];
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    protected function seedOne(array $definition, ?User $admin): void
    {
        $listing = Listing::updateOrCreate(
            ['code' => $definition['code']],
            $definition['listing'],
        );

        $listing->spec()->updateOrCreate(['listing_id' => $listing->id], $definition['spec']);
        $listing->buildingService()->updateOrCreate(['listing_id' => $listing->id], $definition['building_service']);
        $listing->otherInfo()->updateOrCreate(['listing_id' => $listing->id], $definition['other_info']);

        $listing->units()->delete();
        foreach ($definition['units'] as $unit) {
            $listing->units()->create($unit);
        }

        $listing->fees()->delete();
        foreach ($definition['fees'] as $fee) {
            $listing->fees()->create($fee);
        }

        if ($admin !== null) {
            $listing->remarks()->delete();
            $firstUnit = $listing->units()->first();
            foreach ($definition['remarks'] as $remark) {
                $listing->remarks()->create([
                    'user_id' => $admin->id,
                    'listing_unit_id' => ! empty($remark['unit_linked']) ? $firstUnit?->id : null,
                    'comment' => $remark['comment'],
                    'remarked_at' => now()->subDays($remark['days_ago'] ?? 1),
                ]);
            }

            $this->seedAssets($listing, $admin, (string) $definition['code']);
        }
    }

    protected function seedAssets(Listing $listing, User $admin, string $code): void
    {
        $sort = 0;
        foreach (array_keys($this->assetColors) as $assetType) {
            $existing = ListingAsset::query()
                ->where('listing_id', $listing->id)
                ->where('asset_type', $assetType)
                ->first();

            if ($existing !== null) {
                $this->mediaUsage->removeModel($existing);
                $existing->delete();
            }

            $file = $this->makeDemoImage($code.' '.$assetType, $this->assetColors[$assetType]);
            $processed = $this->imageProcessor->process($file);
            $label = $this->registry->label(ListingLookupGroups::IMAGE_TYPE, $assetType);
            $media = $this->mediaUpload->upload($processed, [
                'title' => $code.' '.$label,
            ], $admin->id);

            $asset = $listing->assets()->create([
                'asset_type' => $assetType,
                'media_id' => $media->id,
                'sort_order' => $sort,
            ]);

            $this->mediaUsage->register(
                $media,
                $asset,
                'property-listings',
                'asset_media',
                'Listing Asset',
            );

            $sort += 10;
        }

        $this->mediaUsage->syncRelatedMedia(
            $listing->refresh(),
            'property-listings',
            'asset_media',
            $listing->assets()->pluck('media_id')->all(),
            'Listing Asset',
        );
    }

    protected function makeDemoImage(string $label, string $hexColor): UploadedFile
    {
        if ($this->gdAvailable()) {
            return $this->makeDemoImageWithGd($label, $hexColor);
        }

        return $this->makeDemoImageFallback($label);
    }

    protected function gdAvailable(): bool
    {
        return function_exists('imagecreatetruecolor')
            && function_exists('imagejpeg')
            && function_exists('imagecolorallocate');
    }

    protected function makeDemoImageWithGd(string $label, string $hexColor): UploadedFile
    {
        $width = 640;
        $height = 480;
        $image = imagecreatetruecolor($width, $height);
        [$r, $g, $b] = sscanf(ltrim($hexColor, '#'), '%02x%02x%02x');
        $background = imagecolorallocate($image, (int) $r, (int) $g, (int) $b);
        imagefill($image, 0, 0, $background);
        $white = imagecolorallocate($image, 255, 255, 255);
        imagestring($image, 5, 16, 16, substr($label, 0, 28), $white);

        $path = tempnam(sys_get_temp_dir(), 'demo-listing-').'.jpg';
        imagejpeg($image, $path, 85);
        imagedestroy($image);

        return new UploadedFile($path, basename($path), 'image/jpeg', null, true);
    }

    protected function makeDemoImageFallback(string $label): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'demo-listing-').'.jpg';
        file_put_contents($path, base64_decode(self::FALLBACK_JPEG, true) ?: '');

        $filename = Str::slug(substr($label, 0, 40) ?: 'demo').'.jpg';

        return new UploadedFile($path, $filename, 'image/jpeg', null, true);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function demoListingDefinitions(): array
    {
        return [
            $this->definition(
                'DEMO-001',
                'Pacific Tower One',
                'Metro Manila',
                'Makati City',
                'Bel-Air',
                '123 Ayala Avenue, Makati',
                'Ayala Land Offices',
                'a-plus',
                2019,
                950,
                15000,
            ),
            $this->definition(
                'DEMO-002',
                'Harbor View Plaza',
                'Metro Manila',
                'Pasay City',
                'MOA Complex',
                'Seaside Boulevard, Pasay',
                'SM Prime Holdings',
                'a',
                2021,
                780,
                9800,
            ),
            $this->definition(
                'DEMO-003',
                'Northgate Business Hub',
                'Bulacan',
                'Malolos City',
                'Longos',
                'MacArthur Highway, Malolos',
                'Northgate Developers',
                'b',
                2017,
                520,
                22000,
            ),
            $this->definition(
                'DEMO-004',
                'Cebu IT Park Tower',
                'Cebu',
                'Cebu City',
                'Apas',
                'Salinas Drive, Lahug',
                'Cebu Property Group',
                'a',
                2020,
                650,
                11000,
            ),
            $this->definition(
                'DEMO-005',
                'Davao Riverfront Offices',
                'Davao del Sur',
                'Davao City',
                'Bajada',
                'J.P. Laurel Avenue, Bajada',
                'Mindanao Estates',
                'b',
                2018,
                480,
                8500,
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function definition(
        string $code,
        string $name,
        string $province,
        string $city,
        string $brgy,
        string $address,
        string $developer,
        string $grade,
        int $completionYear,
        float $officeRate,
        float $totalArea,
    ): array {
        return [
            'code' => $code,
            'listing' => [
                'name' => $name,
                'province' => $province,
                'city' => $city,
                'brgy' => $brgy,
                'address' => $address,
                'office_rental_rate' => $officeRate,
                'total_area_size' => $totalArea,
                'unit_market_size' => round($totalArea * 0.12, 2),
                'retail_market_rate' => round($officeRate * 1.15, 2),
                'completion_status' => 'existing',
                'published_to_public' => true,
            ],
            'spec' => [
                'developer' => $developer,
                'grade' => $grade,
                'completion_year' => $completionYear,
                'completion_qtr' => 'Q2',
                'no_of_floors' => '32',
                'no_of_basement' => '4',
                'density_ratio' => '1:450',
                'parking_allocation' => '1:100 sqm',
                'floor_to_ceiling_height' => '2.8m',
                'gross_leasable_area' => round($totalArea * 0.82, 2),
                'typical_floor_area' => round($totalArea / 28, 2),
                'typical_retail_floor_area' => 650,
                'floor_efficiency' => '82% efficient',
            ],
            'building_service' => [
                'operating_hours' => 'Mon–Fri 7AM–7PM',
                'ac_system' => 'Central chilled water',
                'no_of_lifts_passenger' => '8 passenger lifts',
                'no_of_lifts_service' => '2 service lifts',
                'telco' => 'PLDT, Globe, Converge',
                'backup_power' => '100% backup',
            ],
            'other_info' => [
                'peza_accreditation' => 'yes',
                'sustainability' => 'LEED Gold certified with rainwater harvesting.',
                'other_info_visible' => true,
            ],
            'units' => [
                [
                    'floor' => '10F',
                    'unit' => '1001',
                    'area_size' => 185,
                    'rental' => $officeRate,
                    'handover_condition' => 'bare-shell',
                    'availability' => 'vacant',
                    'bedrooms' => null,
                    'selling_price' => null,
                    'property_type' => 'commercial-office',
                    'for_lease' => true,
                    'for_sale' => false,
                    'last_remarks' => 'Corner unit with city view.',
                    'sort_order' => 10,
                ],
                [
                    'floor' => '12F',
                    'unit' => '1205',
                    'area_size' => 240,
                    'rental' => round($officeRate * 1.05, 2),
                    'handover_condition' => 'partially-fitted',
                    'availability' => 'vacant',
                    'bedrooms' => null,
                    'selling_price' => 42000000,
                    'property_type' => 'commercial-office',
                    'for_lease' => true,
                    'for_sale' => true,
                    'last_remarks' => 'Includes pantry fit-out.',
                    'sort_order' => 20,
                ],
                [
                    'floor' => 'GF',
                    'unit' => 'Retail-02',
                    'area_size' => 320,
                    'rental' => round($officeRate * 1.25, 2),
                    'handover_condition' => 'as-is-where-is',
                    'availability' => 'leased',
                    'bedrooms' => null,
                    'selling_price' => null,
                    'property_type' => 'commercial-retail',
                    'for_lease' => true,
                    'for_sale' => false,
                    'last_remarks' => 'High foot traffic frontage.',
                    'sort_order' => 30,
                ],
            ],
            'fees' => [
                ['fee_type' => 'rental-rate', 'fee' => $officeRate, 'sort_order' => 10],
                ['fee_type' => 'dues-cusa', 'fee' => 95, 'sort_order' => 20],
                ['fee_type' => 'parking-fee', 'fee' => 4500, 'sort_order' => 30],
            ],
            'remarks' => [
                ['comment' => 'Initial demo listing seeded for admin UI review.', 'days_ago' => 5, 'unit_linked' => false],
                ['comment' => 'Follow up with broker on vacant units.', 'days_ago' => 2, 'unit_linked' => true],
            ],
        ];
    }
}
