<?php

namespace App\Modules\PropertyListings\Services;

use App\Framework\Admin\List\AdminListDefinition;
use App\Framework\Admin\List\AdminListState;
use App\Modules\PropertyListings\Models\Listing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListingExportService
{
    public function __construct(
        protected ListingAdminListService $listService,
    ) {}

    /**
     * @return array<int, string>
     */
    public function headers(): array
    {
        return [
            'code',
            'name',
            'province',
            'city',
            'brgy',
            'address',
            'office_rental_rate',
            'total_area_size',
            'unit_market_size',
            'retail_market_rate',
            'completion_status',
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
            'unit_id',
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
            'unit_sort_order',
        ];
    }

    public function template(): StreamedResponse
    {
        $filename = 'property-listings-template.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $this->headers());
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function download(Request $request): StreamedResponse
    {
        $definition = $this->listService->definition();
        $state = AdminListState::fromRequest($request, $definition);
        $query = $this->filteredListingQuery($definition, $state);
        $filename = 'property-listings-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $this->headers());

            $query->orderBy('listings.code')->chunk(100, function ($listings) use ($handle) {
                foreach ($listings as $listing) {
                    $this->writeListingRows($handle, $listing);
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
            ->with(['spec', 'buildingService', 'otherInfo', 'units'])
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

    protected function writeListingRows($handle, Listing $listing): void
    {
        if ($listing->units->isEmpty()) {
            fputcsv($handle, $this->rowValues($listing, null));

            return;
        }

        foreach ($listing->units as $unit) {
            fputcsv($handle, $this->rowValues($listing, $unit));
        }
    }

    protected function rowValues(Listing $listing, mixed $unit): array
    {
        $spec = $listing->spec;
        $building = $listing->buildingService;
        $other = $listing->otherInfo;

        return [
            $listing->code,
            $listing->name,
            $listing->province,
            $listing->city,
            $listing->brgy,
            $listing->address,
            $listing->office_rental_rate,
            $listing->total_area_size,
            $listing->unit_market_size,
            $listing->retail_market_rate,
            $listing->completion_status,
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
            $unit?->id,
            $unit?->floor,
            $unit?->unit,
            $unit?->area_size,
            $unit?->rental,
            $unit?->handover_condition,
            $unit?->availability,
            $unit?->bedrooms,
            $unit?->selling_price,
            $unit?->property_type,
            $this->boolExport($unit?->for_lease),
            $this->boolExport($unit?->for_sale),
            $unit?->last_remarks,
            $unit?->sort_order,
        ];
    }

    protected function boolExport(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
    }
}
