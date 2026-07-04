@php
    use App\Modules\PropertyListings\Support\ListingLookupGroups;

    $listingModel = $listing ?? null;
    $spec = $listingModel?->spec;
    $buildingService = $listingModel?->buildingService;
    $otherInfo = $listingModel?->otherInfo;
    $netUsableArea = $listingModel?->netUsableArea();
    $formAction = $formAction ?? route('admin.listings.store');
    $formMethod = strtoupper($formMethod ?? 'POST');
@endphp

@if ($errors->any())
    <div class="alert alert-danger small">
        Please review the highlighted fields and try again.
    </div>
@endif

<div class="listing-editor listing-editor-sections">
    <div class="listing-editor-remarks-toggle-bar">
        <button type="button"
                class="btn btn-sm btn-outline-secondary listing-remarks-toolbar-toggle"
                data-listing-remarks-toggle
                title="Hide remarks"
                aria-expanded="true"
                aria-controls="listing-remarks-panel-body">
            <i class="{{ admin_icon('bi-journal-text') }} me-1" aria-hidden="true"></i>
            <span data-listing-remarks-toggle-label>Hide remarks</span>
        </button>
    </div>
    <form id="listing-edit-form"
          method="POST"
          action="{{ $formAction }}"
          class="listing-editor-form-area">
        @csrf
        @if ($formMethod !== 'POST')
            @method($formMethod)
        @endif

        <div class="listing-editor-col-left">
            <div class="card listing-section-card listing-section-card--identity">
                <div class="card-header py-2 small listing-section-header">Identity</div>
                <div class="card-body p-3">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Code</label>
                            <input type="text"
                                   class="form-control @error('code') is-invalid @enderror"
                                   name="code"
                                   value="{{ old('code', $listingModel?->code) }}"
                                   required>
                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small text-muted">Name</label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   name="name"
                                   value="{{ old('name', $listingModel?->name) }}"
                                   required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Completion Status</label>
                            <select class="form-select @error('completion_status') is-invalid @enderror" name="completion_status">
                                <option value="">—</option>
                                @foreach ($lookups[ListingLookupGroups::COMPLETION_STATUS] ?? [] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('completion_status', $listingModel?->completion_status) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('completion_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card listing-section-card listing-section-card--location">
                <div class="card-header py-2 small listing-section-header">Location</div>
                <div class="card-body p-3">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small text-muted" for="listing_province">Province</label>
                            <select class="form-select @error('province') is-invalid @enderror"
                                    id="listing_province"
                                    name="province"
                                    required>
                                <option value="">Select province...</option>
                                @foreach ($provinces ?? [] as $province)
                                    <option value="{{ $province->name }}"
                                            data-province-id="{{ $province->id }}"
                                            @selected(old('province', $listingModel?->province) === $province->name)>
                                        {{ $province->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('province')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted" for="listing_city">City / Municipality</label>
                            <select class="form-select @error('city') is-invalid @enderror"
                                    id="listing_city"
                                    name="city"
                                    required>
                                <option value="">Select city...</option>
                                @if (old('city', $listingModel?->city))
                                    <option value="{{ old('city', $listingModel?->city) }}" selected>{{ old('city', $listingModel?->city) }}</option>
                                @endif
                            </select>
                            @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Barangay</label>
                            <input type="text"
                                   class="form-control @error('brgy') is-invalid @enderror"
                                   name="brgy"
                                   value="{{ old('brgy', $listingModel?->brgy) }}">
                            @error('brgy')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-muted">Address</label>
                            <textarea class="form-control @error('address') is-invalid @enderror"
                                      name="address"
                                      rows="2">{{ old('address', $listingModel?->address) }}</textarea>
                            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card listing-section-card listing-section-card--building-services">
                <div class="card-header py-2 small listing-section-header">Building Services</div>
                <div class="card-body p-3">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Operating Hours</label>
                            <input type="text"
                                   class="form-control @error('building_service.operating_hours') is-invalid @enderror"
                                   name="building_service[operating_hours]"
                                   value="{{ old('building_service.operating_hours', $buildingService?->operating_hours) }}">
                            @error('building_service.operating_hours')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">AC System</label>
                            <input type="text"
                                   class="form-control @error('building_service.ac_system') is-invalid @enderror"
                                   name="building_service[ac_system]"
                                   value="{{ old('building_service.ac_system', $buildingService?->ac_system) }}">
                            @error('building_service.ac_system')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Passenger Lifts</label>
                            <input type="number"
                                   class="form-control @error('building_service.no_of_lifts_passenger') is-invalid @enderror"
                                   name="building_service[no_of_lifts_passenger]"
                                   value="{{ old('building_service.no_of_lifts_passenger', $buildingService?->no_of_lifts_passenger) }}">
                            @error('building_service.no_of_lifts_passenger')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Service Lifts</label>
                            <input type="number"
                                   class="form-control @error('building_service.no_of_lifts_service') is-invalid @enderror"
                                   name="building_service[no_of_lifts_service]"
                                   value="{{ old('building_service.no_of_lifts_service', $buildingService?->no_of_lifts_service) }}">
                            @error('building_service.no_of_lifts_service')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Backup Power</label>
                            <input type="number"
                                   class="form-control @error('building_service.backup_power') is-invalid @enderror"
                                   name="building_service[backup_power]"
                                   value="{{ old('building_service.backup_power', $buildingService?->backup_power) }}">
                            @error('building_service.backup_power')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Telco</label>
                            <input type="text"
                                   class="form-control @error('building_service.telco') is-invalid @enderror"
                                   name="building_service[telco]"
                                   value="{{ old('building_service.telco', $buildingService?->telco) }}">
                            @error('building_service.telco')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="listing-editor-col-middle">
            <div class="card listing-section-card listing-section-card--listing-specs">
                <div class="card-header py-2 small listing-section-header">Listing Specs</div>
                <div class="card-body p-3">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Developer</label>
                            <input type="text"
                                   class="form-control @error('spec.developer') is-invalid @enderror"
                                   name="spec[developer]"
                                   value="{{ old('spec.developer', $spec?->developer) }}">
                            @error('spec.developer')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Grade</label>
                            <select class="form-select @error('spec.grade') is-invalid @enderror" name="spec[grade]">
                                <option value="">—</option>
                                @foreach ($lookups[ListingLookupGroups::GRADE] ?? [] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('spec.grade', $spec?->grade) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('spec.grade')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted">Completion Year</label>
                            <input type="number"
                                   class="form-control @error('spec.completion_year') is-invalid @enderror"
                                   name="spec[completion_year]"
                                   value="{{ old('spec.completion_year', $spec?->completion_year) }}">
                            @error('spec.completion_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted">Completion Qtr</label>
                            <input type="text"
                                   class="form-control @error('spec.completion_qtr') is-invalid @enderror"
                                   name="spec[completion_qtr]"
                                   value="{{ old('spec.completion_qtr', $spec?->completion_qtr) }}">
                            @error('spec.completion_qtr')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted">No. of Floors</label>
                            <input type="text"
                                   class="form-control @error('spec.no_of_floors') is-invalid @enderror"
                                   name="spec[no_of_floors]"
                                   value="{{ old('spec.no_of_floors', $spec?->no_of_floors) }}">
                            @error('spec.no_of_floors')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted">No. of Basement</label>
                            <input type="text"
                                   class="form-control @error('spec.no_of_basement') is-invalid @enderror"
                                   name="spec[no_of_basement]"
                                   value="{{ old('spec.no_of_basement', $spec?->no_of_basement) }}">
                            @error('spec.no_of_basement')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Density Ratio</label>
                            <input type="text"
                                   class="form-control @error('spec.density_ratio') is-invalid @enderror"
                                   name="spec[density_ratio]"
                                   value="{{ old('spec.density_ratio', $spec?->density_ratio) }}">
                            @error('spec.density_ratio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Parking Allocation</label>
                            <input type="text"
                                   class="form-control @error('spec.parking_allocation') is-invalid @enderror"
                                   name="spec[parking_allocation]"
                                   value="{{ old('spec.parking_allocation', $spec?->parking_allocation) }}">
                            @error('spec.parking_allocation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Floor to Ceiling Height</label>
                            <input type="text"
                                   class="form-control @error('spec.floor_to_ceiling_height') is-invalid @enderror"
                                   name="spec[floor_to_ceiling_height]"
                                   value="{{ old('spec.floor_to_ceiling_height', $spec?->floor_to_ceiling_height) }}">
                            @error('spec.floor_to_ceiling_height')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Gross Leasable Area</label>
                            <input type="number"
                                   step="0.01"
                                   class="form-control @error('spec.gross_leasable_area') is-invalid @enderror"
                                   name="spec[gross_leasable_area]"
                                   value="{{ old('spec.gross_leasable_area', $spec?->gross_leasable_area) }}">
                            @error('spec.gross_leasable_area')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Typical Floor Area</label>
                            <input type="number"
                                   step="0.01"
                                   class="form-control @error('spec.typical_floor_area') is-invalid @enderror"
                                   name="spec[typical_floor_area]"
                                   value="{{ old('spec.typical_floor_area', $spec?->typical_floor_area) }}">
                            @error('spec.typical_floor_area')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Typical Retail Floor Area</label>
                            <input type="number"
                                   step="0.01"
                                   class="form-control @error('spec.typical_retail_floor_area') is-invalid @enderror"
                                   name="spec[typical_retail_floor_area]"
                                   value="{{ old('spec.typical_retail_floor_area', $spec?->typical_retail_floor_area) }}"
                                   data-listing-net-usable-retail>
                            @error('spec.typical_retail_floor_area')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Floor Efficiency (%)</label>
                            <input type="number"
                                   step="0.01"
                                   class="form-control @error('spec.floor_efficiency') is-invalid @enderror"
                                   name="spec[floor_efficiency]"
                                   value="{{ old('spec.floor_efficiency', $spec?->floor_efficiency) }}"
                                   data-listing-net-usable-efficiency>
                            @error('spec.floor_efficiency')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Net Usable Area</label>
                            <input type="text"
                                   class="form-control bg-light"
                                   value="{{ $netUsableArea !== null ? number_format($netUsableArea, 2) : '—' }}"
                                   readonly
                                   data-listing-net-usable-output>
                            <div class="form-text">Computed from typical retail floor area × efficiency.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card listing-section-card listing-section-card--rates-sizing">
                <div class="card-header py-2 small listing-section-header">Rates &amp; Sizing</div>
                <div class="card-body p-3">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Office Rental Rate</label>
                            <input type="number"
                                   step="0.01"
                                   class="form-control @error('office_rental_rate') is-invalid @enderror"
                                   name="office_rental_rate"
                                   value="{{ old('office_rental_rate', $listingModel?->office_rental_rate) }}">
                            @error('office_rental_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Total Area Size</label>
                            <input type="number"
                                   step="0.01"
                                   class="form-control @error('total_area_size') is-invalid @enderror"
                                   name="total_area_size"
                                   value="{{ old('total_area_size', $listingModel?->total_area_size) }}">
                            @error('total_area_size')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Unit Market Size</label>
                            <input type="number"
                                   step="0.01"
                                   class="form-control @error('unit_market_size') is-invalid @enderror"
                                   name="unit_market_size"
                                   value="{{ old('unit_market_size', $listingModel?->unit_market_size) }}">
                            @error('unit_market_size')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Retail Market Rate</label>
                            <input type="number"
                                   step="0.01"
                                   class="form-control @error('retail_market_rate') is-invalid @enderror"
                                   name="retail_market_rate"
                                   value="{{ old('retail_market_rate', $listingModel?->retail_market_rate) }}">
                            @error('retail_market_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card listing-section-card listing-section-card--other-services">
                <div class="card-header py-2 small listing-section-header">Other Services</div>
                <div class="card-body p-3">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small text-muted">PEZA Accreditation</label>
                            <select class="form-select @error('other_info.peza_accreditation') is-invalid @enderror"
                                    name="other_info[peza_accreditation]">
                                <option value="">—</option>
                                @foreach ($lookups[ListingLookupGroups::PEZA_ACCREDITATION] ?? [] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('other_info.peza_accreditation', $otherInfo?->peza_accreditation) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('other_info.peza_accreditation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check mb-2">
                                <input type="hidden" name="other_info[other_info_visible]" value="0">
                                <input type="checkbox"
                                       class="form-check-input"
                                       id="other_info_visible"
                                       name="other_info[other_info_visible]"
                                       value="1"
                                       @checked(old('other_info.other_info_visible', $otherInfo?->other_info_visible ?? true))>
                                <label class="form-check-label" for="other_info_visible">Visible on public site</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-muted">Sustainability</label>
                            <textarea class="form-control @error('other_info.sustainability') is-invalid @enderror"
                                      name="other_info[sustainability]"
                                      rows="3">{{ old('other_info.sustainability', $otherInfo?->sustainability) }}</textarea>
                            @error('other_info.sustainability')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="listing-editor-tabs mt-2">
        <div class="card-header p-0 border-0">
            <ul class="nav nav-tabs card-header-tabs px-2 pt-2 listing-relation-tabs" id="listingRelationTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active py-2 px-3 small listing-tab-units" id="listing-units-tab" data-bs-toggle="tab"
                            data-bs-target="#listing-units-panel" type="button" role="tab">
                        <i class="{{ admin_icon('bi-building') }} me-1" aria-hidden="true"></i>Units
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-2 px-3 small listing-tab-fees" id="listing-fees-tab" data-bs-toggle="tab"
                            data-bs-target="#listing-fees-panel" type="button" role="tab">
                        <i class="{{ admin_icon('bi-cash-coin') }} me-1" aria-hidden="true"></i>Fees
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-2 px-3 small listing-tab-assets" id="listing-assets-tab" data-bs-toggle="tab"
                            data-bs-target="#listing-assets-panel" type="button" role="tab">
                        <i class="{{ admin_icon('bi-images') }} me-1" aria-hidden="true"></i>Assets
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body p-3 listing-editor-tabs-body">
            <div class="tab-content">
                <div class="tab-pane fade show active listing-pane-units" id="listing-units-panel" role="tabpanel">
                    @include('propertylistings::listings._units-tab')
                </div>
                <div class="tab-pane fade listing-pane-fees" id="listing-fees-panel" role="tabpanel">
                    @include('propertylistings::listings._fees-tab')
                </div>
                <div class="tab-pane fade listing-pane-assets" id="listing-assets-panel" role="tabpanel">
                    @include('propertylistings::listings._assets-tab')
                </div>
            </div>
        </div>
        </div>
    </form>

    @if ($listingModel?->exists && auth()->user()?->can('batchAssets', App\Modules\PropertyListings\Models\Listing::class))
        <form id="listing-assets-upload-form"
              method="POST"
              action="{{ route('admin.listings.assets.batch.listing.upload', $listingModel) }}"
              enctype="multipart/form-data"
              class="visually-hidden">
            @csrf
        </form>
    @endif

    <aside class="listing-editor-remarks">
        @include('propertylistings::listings._remarks-sidebar')
    </aside>
</div>

@push('styles')
<style>
    .listing-editor-sections {
        display: grid;
        grid-template-columns: 1fr 1fr minmax(280px, 320px);
        grid-template-areas:
            "toolbar toolbar toolbar"
            "left middle remarks"
            "tabs tabs tabs";
        gap: 0.5rem;
        align-items: start;
    }

    .listing-editor-remarks-toggle-bar {
        grid-area: toolbar;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        margin-bottom: 0.25rem;
    }

    .listing-editor-form-area {
        display: contents;
    }

    .listing-editor-col-left {
        grid-area: left;
    }

    .listing-editor-col-middle {
        grid-area: middle;
    }

    .listing-editor-tabs {
        grid-area: tabs;
    }

    .listing-editor-remarks {
        grid-area: remarks;
        align-self: stretch;
    }

    .listing-editor-col-left .card + .card,
    .listing-editor-col-middle .card + .card {
        margin-top: 0.5rem;
    }

    .admin-body .listing-editor .card-header.listing-section-header {
        background-color: #f1f5f9 !important;
        color: #0f172a !important;
        border-bottom: 1px solid #e2e8f0 !important;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: none;
    }

    .listing-editor .listing-section-card {
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }

    .listing-editor .listing-section-card > .card-body.p-3 {
        background-color: #fff !important;
    }

    .listing-editor .listing-section-card--identity {
        border-color: #93c5fd;

        .listing-section-header {
            background-color: #dbeafe !important;
            border-bottom-color: #93c5fd !important;
        }

        > .card-body.p-3 {
            background-color: #eff6ff !important;
        }
    }

    .listing-editor .listing-section-card--location {
        border-color: #6ee7b7;

        .listing-section-header {
            background-color: #d1fae5 !important;
            border-bottom-color: #6ee7b7 !important;
        }

        > .card-body.p-3 {
            background-color: #ecfdf5 !important;
        }
    }

    .listing-editor .listing-section-card--building-services {
        border-color: #c4b5fd;

        .listing-section-header {
            background-color: #ede9fe !important;
            border-bottom-color: #c4b5fd !important;
        }

        > .card-body.p-3 {
            background-color: #f5f3ff !important;
        }
    }

    .listing-editor .listing-section-card--listing-specs {
        border-color: #fcd34d;

        .listing-section-header {
            background-color: #fef3c7 !important;
            border-bottom-color: #fcd34d !important;
        }

        > .card-body.p-3 {
            background-color: #fffbeb !important;
        }
    }

    .listing-editor .listing-section-card--rates-sizing {
        border-color: #67e8f9;

        .listing-section-header {
            background-color: #cffafe !important;
            border-bottom-color: #67e8f9 !important;
        }

        > .card-body.p-3 {
            background-color: #ecfeff !important;
        }
    }

    .listing-editor .listing-section-card--other-services {
        border-color: #f9a8d4;

        .listing-section-header {
            background-color: #fce7f3 !important;
            border-bottom-color: #f9a8d4 !important;
        }

        > .card-body.p-3 {
            background-color: #fdf2f8 !important;
        }
    }

    .listing-editor-tabs {
        border: 1px solid #cbd5e1;
        border-radius: 0.375rem;
        overflow: hidden;
        background-color: #fff;
    }

    .listing-editor-tabs > .card-body.p-3.listing-editor-tabs-body {
        background-color: #f8fafc !important;
    }

    .listing-editor-sections.is-remarks-hidden {
        grid-template-columns: 1fr 1fr;
        grid-template-areas:
            "toolbar toolbar"
            "left middle"
            "tabs tabs";
    }

    .listing-editor-sections.is-remarks-hidden .listing-editor-remarks {
        display: none !important;
    }

    .listing-editor-tabs {
        grid-area: tabs;
        width: 100%;
    }

    .listing-pane-units,
    .listing-pane-fees,
    .listing-pane-assets {
        border: none;
        border-radius: 0.375rem;
        padding: 0.75rem;
    }

    .listing-pane-units {
        background-color: rgba(13, 110, 253, 0.08);
    }

    .listing-pane-fees {
        background-color: rgba(255, 193, 7, 0.12);
    }

    .listing-pane-assets {
        background-color: rgba(13, 202, 240, 0.08);
    }

    .listing-editor-tabs > .card-body {
        background-color: transparent !important;
    }

    .listing-editor-tabs > .card-body.listing-editor-tabs-body {
        background-color: #f8fafc !important;
    }

    .listing-pane-assets .listing-assets-upload-pane.card,
    .listing-pane-assets .listing-assets-upload-pane .card-body {
        background-color: rgba(13, 202, 240, 0.08) !important;
    }

    .listing-pane-assets .listing-assets-upload-pane .card-header {
        background-color: rgba(13, 202, 240, 0.12) !important;
        border-bottom-color: rgba(13, 202, 240, 0.2);
    }

    .listing-relation-tabs .nav-link {
        font-weight: 500;
        border-bottom-width: 2px;
    }

    .listing-relation-tabs .listing-tab-units {
        color: #0d6efd;
        background-color: rgba(13, 110, 253, 0.08);
    }

    .listing-relation-tabs .listing-tab-units.active {
        color: #0d6efd;
        font-weight: 600;
        background-color: rgba(13, 110, 253, 0.18);
        border-color: #dee2e6 #dee2e6 #fff;
        border-bottom: 3px solid #0d6efd;
    }

    .listing-relation-tabs .listing-tab-fees {
        color: #997404;
        background-color: rgba(255, 193, 7, 0.08);
    }

    .listing-relation-tabs .listing-tab-fees.active {
        color: #997404;
        font-weight: 600;
        background-color: rgba(255, 193, 7, 0.22);
        border-color: #dee2e6 #dee2e6 #fff;
        border-bottom: 3px solid #ffc107;
    }

    .listing-relation-tabs .listing-tab-assets {
        color: #055160;
        background-color: rgba(13, 202, 240, 0.08);
    }

    .listing-relation-tabs .listing-tab-assets.active {
        color: #055160;
        font-weight: 600;
        background-color: rgba(13, 202, 240, 0.18);
        border-color: #dee2e6 #dee2e6 #fff;
        border-bottom: 3px solid #0dcaf0;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const provinceSelect = document.getElementById('listing_province');
    const citySelect = document.getElementById('listing_city');
    const selectedCity = @json(old('city', $listingModel?->city));

    async function loadCities(provinceOption, preserveCity = null) {
        if (!citySelect || !provinceOption) {
            return;
        }

        citySelect.innerHTML = '<option value="">Select city...</option>';

        const provinceId = provinceOption.dataset.provinceId;
        if (!provinceId) {
            return;
        }

        citySelect.disabled = true;

        try {
            const response = await fetch(`{{ url('/admin/cities/by-province') }}/${provinceId}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();

            data.cities.forEach((city) => {
                const option = document.createElement('option');
                option.value = city.name;
                option.textContent = city.name;
                if (preserveCity && preserveCity === city.name) {
                    option.selected = true;
                }
                citySelect.appendChild(option);
            });
        } finally {
            citySelect.disabled = false;
        }
    }

    provinceSelect?.addEventListener('change', () => {
        loadCities(provinceSelect.selectedOptions[0]);
    });

    if (provinceSelect?.value && citySelect && citySelect.options.length <= 1) {
        loadCities(provinceSelect.selectedOptions[0], selectedCity);
    }

    const retailInput = document.querySelector('[data-listing-net-usable-retail]');
    const efficiencyInput = document.querySelector('[data-listing-net-usable-efficiency]');
    const output = document.querySelector('[data-listing-net-usable-output]');

    const updateNetUsable = () => {
        if (!output || !retailInput || !efficiencyInput) {
            return;
        }

        const retail = parseFloat(retailInput.value);
        const efficiency = parseFloat(efficiencyInput.value);

        if (Number.isFinite(retail) && Number.isFinite(efficiency)) {
            output.value = (retail * (efficiency / 100)).toFixed(2);
        } else {
            output.value = '—';
        }
    };

    retailInput?.addEventListener('input', updateNetUsable);
    efficiencyInput?.addEventListener('input', updateNetUsable);
});
</script>
@endpush
