<?php

namespace App\Modules\PropertyListings\Services;

use App\Framework\Admin\List\AdminListAction;
use App\Framework\Admin\List\AdminListColumn;
use App\Framework\Admin\List\AdminListDefinition;
use App\Framework\Admin\List\AdminListFilter;
use App\Framework\Admin\List\AdminListResult;
use App\Framework\Admin\List\AdminListService;
use App\Framework\Admin\List\AdminListState;
use App\Modules\Address\Models\City;
use App\Modules\Address\Models\Province;
use App\Modules\PropertyListings\Models\Listing;
use App\Modules\PropertyListings\Support\ListingLookupGroups;
use App\Modules\PropertyListings\Support\ListingLookupRegistry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ListingAdminListService
{
    protected ?AdminListState $lastState = null;

    public function __construct(
        protected AdminListService $lists,
        protected ListingLookupRegistry $registry,
    ) {}

    public function result(Request $request): AdminListResult
    {
        $definition = $this->definition();
        $this->lastState = AdminListState::fromRequest($request, $definition);

        $query = Listing::query()
            ->with(['spec'])
            ->withCount('units')
            ->leftJoin('listing_specs', 'listing_specs.listing_id', '=', 'listings.id')
            ->select('listings.*');

        return $this->lists->build($query, $definition, $request);
    }

    public function definition(): AdminListDefinition
    {
        return new AdminListDefinition(
            id: 'listings',
            title: 'Listings',
            modelClass: Listing::class,
            columns: [
                AdminListColumn::make('no', 'No', class: 'text-muted small', headerClass: 'text-muted'),
                AdminListColumn::make('code', 'Code', fn (Listing $listing) => sprintf(
                    '<a href="%s" class="text-decoration-none fw-semibold">%s</a>',
                    route('admin.listings.edit', $listing),
                    e($listing->code),
                ), sortField: 'listings.code', raw: true),
                AdminListColumn::make('name', 'Name', fn (Listing $listing) => e($listing->name), sortField: 'listings.name'),
                AdminListColumn::make('city', 'City', fn (Listing $listing) => e($listing->city), sortField: 'listings.city', class: 'small'),
                AdminListColumn::make('completion_status', 'Completion', fn (Listing $listing) => e(
                    $this->registry->label(ListingLookupGroups::COMPLETION_STATUS, $listing->completion_status),
                ), sortField: 'listings.completion_status', class: 'small'),
                AdminListColumn::make('units_count', 'Units', fn (Listing $listing) => (string) $listing->units_count, sortField: 'units_count', class: 'small text-muted'),
                AdminListColumn::make('match_summary', 'Match Summary', fn (Listing $listing) => e($this->matchSummary($listing)), class: 'small text-muted'),
                AdminListColumn::make('compare', '', fn (Listing $listing) => view('propertylistings::listings._compare-toggle', [
                    'listing' => $listing,
                    'class' => 'btn btn-sm btn-outline-secondary admin-icon-btn',
                ])->render(), class: 'text-center', headerClass: 'text-muted', raw: true),
            ],
            filters: [
                AdminListFilter::make(
                    'province',
                    'Province',
                    'select',
                    fn () => Province::query()->active()->orderBy('name')->pluck('name', 'name')->all(),
                    fn (Builder $query, mixed $value) => $query->where('listings.province', $value),
                ),
                AdminListFilter::make(
                    'city',
                    'City',
                    'select',
                    fn () => City::query()->active()->orderBy('name')->pluck('name', 'name')->all(),
                    fn (Builder $query, mixed $value) => $query->where('listings.city', $value),
                ),
                AdminListFilter::make(
                    'completion_status',
                    'Completion Status',
                    'select',
                    fn () => $this->registry->options(ListingLookupGroups::COMPLETION_STATUS),
                    fn (Builder $query, mixed $value) => $query->where('listings.completion_status', $value),
                ),
                AdminListFilter::make(
                    'grade',
                    'Grade',
                    'select',
                    fn () => $this->registry->options(ListingLookupGroups::GRADE),
                    fn (Builder $query, mixed $value) => $query->where('listing_specs.grade', $value),
                ),
                AdminListFilter::make(
                    'developer',
                    'Developer',
                    'text',
                    query: fn (Builder $query, mixed $value) => $query->where('listing_specs.developer', 'like', '%'.$value.'%'),
                ),
                AdminListFilter::make(
                    'unit_property_type',
                    'Unit Property Type',
                    'select',
                    fn () => $this->registry->options(ListingLookupGroups::PROPERTY_TYPE),
                    fn (Builder $query, mixed $value) => $query->whereHas('units', fn (Builder $unitQuery) => $unitQuery->where('property_type', $value)),
                ),
                AdminListFilter::make(
                    'unit_availability',
                    'Unit Availability',
                    'select',
                    fn () => $this->registry->options(ListingLookupGroups::AVAILABILITY),
                    fn (Builder $query, mixed $value) => $query->whereHas('units', fn (Builder $unitQuery) => $unitQuery->where('availability', $value)),
                ),
                AdminListFilter::make(
                    'unit_handover',
                    'Unit Handover',
                    'select',
                    fn () => $this->registry->options(ListingLookupGroups::HANDOVER_CONDITION),
                    fn (Builder $query, mixed $value) => $query->whereHas('units', fn (Builder $unitQuery) => $unitQuery->where('handover_condition', $value)),
                ),
                AdminListFilter::make(
                    'unit_bedrooms',
                    'Unit Bedrooms',
                    'select',
                    fn () => $this->registry->options(ListingLookupGroups::BEDROOMS),
                    fn (Builder $query, mixed $value) => $query->whereHas('units', fn (Builder $unitQuery) => $unitQuery->where('bedrooms', $value)),
                ),
                AdminListFilter::make(
                    'unit_for_lease',
                    'Unit For Lease',
                    'select',
                    ['1' => 'Yes', '0' => 'No'],
                    fn (Builder $query, mixed $value) => $query->whereHas(
                        'units',
                        fn (Builder $unitQuery) => $unitQuery->where('for_lease', $value === '1'),
                    ),
                ),
                AdminListFilter::make(
                    'unit_for_sale',
                    'Unit For Sale',
                    'select',
                    ['1' => 'Yes', '0' => 'No'],
                    fn (Builder $query, mixed $value) => $query->whereHas(
                        'units',
                        fn (Builder $unitQuery) => $unitQuery->where('for_sale', $value === '1'),
                    ),
                ),
                AdminListFilter::make(
                    'unit_rental_min',
                    'Unit Rental Min',
                    'number',
                    query: fn (Builder $query, mixed $value) => $query->whereHas(
                        'units',
                        fn (Builder $unitQuery) => $unitQuery->where('rental', '>=', (float) $value),
                    ),
                ),
                AdminListFilter::make(
                    'unit_rental_max',
                    'Unit Rental Max',
                    'number',
                    query: fn (Builder $query, mixed $value) => $query->whereHas(
                        'units',
                        fn (Builder $unitQuery) => $unitQuery->where('rental', '<=', (float) $value),
                    ),
                ),
                AdminListFilter::make(
                    'unit_area_min',
                    'Unit Area Min',
                    'number',
                    query: fn (Builder $query, mixed $value) => $query->whereHas(
                        'units',
                        fn (Builder $unitQuery) => $unitQuery->where('area_size', '>=', (float) $value),
                    ),
                ),
                AdminListFilter::make(
                    'unit_area_max',
                    'Unit Area Max',
                    'number',
                    query: fn (Builder $query, mixed $value) => $query->whereHas(
                        'units',
                        fn (Builder $unitQuery) => $unitQuery->where('area_size', '<=', (float) $value),
                    ),
                ),
                AdminListFilter::make(
                    'unit_floor',
                    'Unit Floor',
                    'text',
                    query: fn (Builder $query, mixed $value) => $query->whereHas(
                        'units',
                        fn (Builder $unitQuery) => $unitQuery->where('floor', 'like', '%'.$value.'%'),
                    ),
                ),
            ],
            rowActions: [
                AdminListAction::make(
                    'edit',
                    'Edit',
                    'bi-pencil',
                    fn (Listing $listing) => route('admin.listings.edit', $listing),
                    ability: 'update',
                ),
                AdminListAction::make(
                    'delete',
                    'Delete',
                    'bi-trash',
                    fn (Listing $listing) => route('admin.listings.destroy', $listing),
                    method: 'DELETE',
                    ability: 'delete',
                    confirm: 'Delete this listing and all related units, fees, assets, and remarks?',
                    danger: true,
                ),
            ],
            searchQuery: fn (Builder $query, string $search) => $this->applySearch($query, $search),
            searchPlaceholder: 'Search code, name, address, province, city, or developer...',
            defaultSort: 'code',
            defaultDirection: 'asc',
            defaultPerPage: 25,
        );
    }

    protected function applySearch(Builder $query, string $search): void
    {
        $query->where(function (Builder $query) use ($search) {
            $query->where('listings.code', 'like', "%{$search}%")
                ->orWhere('listings.name', 'like', "%{$search}%")
                ->orWhere('listings.address', 'like', "%{$search}%")
                ->orWhere('listings.province', 'like', "%{$search}%")
                ->orWhere('listings.city', 'like', "%{$search}%")
                ->orWhere('listings.brgy', 'like', "%{$search}%")
                ->orWhere('listing_specs.developer', 'like', "%{$search}%");
        });
    }

    protected function matchSummary(Listing $listing): string
    {
        if ($this->lastState === null || ! $this->hasActiveUnitFilters()) {
            return '—';
        }

        $query = $listing->units()->getQuery();

        foreach ($this->unitFilterMap() as $key => $callback) {
            $value = $this->lastState->filters[$key] ?? null;

            if ($value === null || $value === '') {
                continue;
            }

            $callback($query, $value);
        }

        $count = $query->count();

        if ($count === 0) {
            return 'No matching units';
        }

        $availability = $this->lastState->filters['unit_availability'] ?? null;
        if ($availability !== null && $availability !== '') {
            $label = $this->registry->label(ListingLookupGroups::AVAILABILITY, (string) $availability);

            return "{$count} {$label}";
        }

        return "{$count} matching unit".($count === 1 ? '' : 's');
    }

    protected function hasActiveUnitFilters(): bool
    {
        if ($this->lastState === null) {
            return false;
        }

        foreach (array_keys($this->unitFilterMap()) as $key) {
            $value = $this->lastState->filters[$key] ?? null;

            if ($value !== null && $value !== '') {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, \Closure(Builder, mixed): void>
     */
    protected function unitFilterMap(): array
    {
        return [
            'unit_property_type' => fn (Builder $query, mixed $value) => $query->where('property_type', $value),
            'unit_availability' => fn (Builder $query, mixed $value) => $query->where('availability', $value),
            'unit_handover' => fn (Builder $query, mixed $value) => $query->where('handover_condition', $value),
            'unit_bedrooms' => fn (Builder $query, mixed $value) => $query->where('bedrooms', $value),
            'unit_for_lease' => fn (Builder $query, mixed $value) => $query->where('for_lease', $value === '1'),
            'unit_for_sale' => fn (Builder $query, mixed $value) => $query->where('for_sale', $value === '1'),
            'unit_rental_min' => fn (Builder $query, mixed $value) => $query->where('rental', '>=', (float) $value),
            'unit_rental_max' => fn (Builder $query, mixed $value) => $query->where('rental', '<=', (float) $value),
            'unit_area_min' => fn (Builder $query, mixed $value) => $query->where('area_size', '>=', (float) $value),
            'unit_area_max' => fn (Builder $query, mixed $value) => $query->where('area_size', '<=', (float) $value),
            'unit_floor' => fn (Builder $query, mixed $value) => $query->where('floor', 'like', '%'.$value.'%'),
        ];
    }
}
