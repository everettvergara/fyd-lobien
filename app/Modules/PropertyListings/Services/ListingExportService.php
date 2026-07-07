<?php

namespace App\Modules\PropertyListings\Services;

use App\Framework\Admin\List\AdminListDefinition;
use App\Framework\Admin\List\AdminListState;
use App\Modules\PropertyListings\Models\Listing;
use App\Modules\PropertyListings\Models\ListingFee;
use App\Modules\PropertyListings\Models\ListingUnit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListingExportService
{
    public function __construct(
        protected ListingAdminListService $listService,
    ) {}

    /**
     * @return array<int, string>
     */
    public function headers(string $type = 'header'): array
    {
        return match ($this->normalizeType($type)) {
            'header' => [
                'code',
                'name',
                'summary',
                'description',
                'slug',
                'province',
                'city',
                'brgy',
                'address',
                'office_rental_rate',
                'total_area_size',
                'unit_market_size',
                'retail_market_rate',
                'completion_status',
                'published_to_public',
                'developer',
                'grade',
                'completion_year',
                'completion_qtr',
                'no_of_floors',
                'no_of_basement',
                'density_ratio',
                'parking_allocation',
                'floor_to_ceiling_height',
                'gross_leasable_area',
                'typical_floor_area',
                'typical_retail_floor_area',
                'floor_efficiency',
                'operating_hours',
                'ac_system',
                'no_of_lifts_passenger',
                'no_of_lifts_service',
                'telco',
                'backup_power',
                'peza_accreditation',
                'sustainability',
                'other_info_visible',
            ],
            'units' => [
                'code',
                'floor',
                'unit',
                'area_size',
                'rental',
                'handover_condition',
                'availability',
                'bedrooms',
                'selling_price',
                'property_type',
                'for_lease',
                'for_sale',
                'last_remarks',
                'sort_order',
            ],
            'fees' => [
                'code',
                'fee_type',
                'fee',
                'sort_order',
            ],
        };
    }

    public function template(string $type = 'header'): StreamedResponse
    {
        $type = $this->normalizeType($type);
        $filename = "property-listings-{$type}-template.csv";

        return response()->streamDownload(function () use ($type) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $this->headers($type));
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function download(Request $request, string $type = 'header'): StreamedResponse
    {
        $type = $this->normalizeType($type);
        $definition = $this->listService->definition();
        $state = AdminListState::fromRequest($request, $definition);
        $query = $this->filteredListingQuery($definition, $state);
        $filename = "property-listings-{$type}-".now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($query, $type) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $this->headers($type));

            $query->orderBy('listings.code')->chunk(100, function ($listings) use ($handle, $type) {
                foreach ($listings as $listing) {
                    $this->writeRows($handle, $listing, $type);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    protected function filteredListingQuery(AdminListDefinition $definition, AdminListState $state): Builder
    {
        $query = Listing::query()
            ->with(['spec', 'buildingService', 'otherInfo', 'units', 'fees'])
            ->leftJoin('listing_specs', 'listing_specs.listing_id', '=', 'listings.id')
            ->select('listings.*');

        if ($state->search && $definition->searchQuery) {
            call_user_func($definition->searchQuery, $query, $state->search);
        }

        foreach ($state->activeFilters() as $key => $value) {
            $definition->filter($key)?->apply($query, $value);
        }

        return $query;
    }

    protected function writeRows($handle, Listing $listing, string $type): void
    {
        match ($type) {
            'header' => fputcsv($handle, $this->headerRowValues($listing)),
            'units' => $this->writeUnitRows($handle, $listing),
            'fees' => $this->writeFeeRows($handle, $listing),
            default => throw new InvalidArgumentException("Unsupported export type [{$type}]."),
        };
    }

    protected function writeUnitRows($handle, Listing $listing): void
    {
        foreach ($listing->units as $unit) {
            fputcsv($handle, $this->unitRowValues($listing, $unit));
        }
    }

    protected function writeFeeRows($handle, Listing $listing): void
    {
        foreach ($listing->fees as $fee) {
            fputcsv($handle, $this->feeRowValues($listing, $fee));
        }
    }

    protected function headerRowValues(Listing $listing): array
    {
        $spec = $listing->spec;
        $building = $listing->buildingService;
        $other = $listing->otherInfo;

        return [
            $listing->code,
            $listing->name,
            $listing->summary,
            $listing->description,
            $listing->slug,
            $listing->province,
            $listing->city,
            $listing->brgy,
            $listing->address,
            $listing->office_rental_rate,
            $listing->total_area_size,
            $listing->unit_market_size,
            $listing->retail_market_rate,
            $listing->completion_status,
            $this->boolExport($listing->published_to_public),
            $spec?->developer,
            $spec?->grade,
            $spec?->completion_year,
            $spec?->completion_qtr,
            $spec?->no_of_floors,
            $spec?->no_of_basement,
            $spec?->density_ratio,
            $spec?->parking_allocation,
            $spec?->floor_to_ceiling_height,
            $spec?->gross_leasable_area,
            $spec?->typical_floor_area,
            $spec?->typical_retail_floor_area,
            $spec?->floor_efficiency,
            $building?->operating_hours,
            $building?->ac_system,
            $building?->no_of_lifts_passenger,
            $building?->no_of_lifts_service,
            $building?->telco,
            $building?->backup_power,
            $other?->peza_accreditation,
            $other?->sustainability,
            $this->boolExport($other?->other_info_visible),
        ];
    }

    protected function unitRowValues(Listing $listing, ListingUnit $unit): array
    {
        return [
            $listing->code,
            $unit->floor,
            $unit->unit,
            $unit->area_size,
            $unit->rental,
            $unit->handover_condition,
            $unit->availability,
            $unit->bedrooms,
            $unit->selling_price,
            $unit->property_type,
            $this->boolExport($unit->for_lease),
            $this->boolExport($unit->for_sale),
            $unit->last_remarks,
            $unit->sort_order,
        ];
    }

    protected function feeRowValues(Listing $listing, ListingFee $fee): array
    {
        return [
            $listing->code,
            $fee->fee_type,
            $fee->fee,
            $fee->sort_order,
        ];
    }

    protected function normalizeType(string $type): string
    {
        $type = strtolower(trim($type));

        if ($type === 'properties' || $type === 'listings') {
            return 'header';
        }

        if (! in_array($type, ['header', 'units', 'fees'], true)) {
            throw new InvalidArgumentException("Unsupported CSV type [{$type}].");
        }

        return $type;
    }

    protected function boolExport(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
    }
}
