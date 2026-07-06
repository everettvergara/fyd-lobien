@extends('admin.layouts.app')

@section('title', 'Property Uploaders')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Property Uploaders'],
    ]" />

    <x-admin.page-header title="Property Uploaders" />

    <p class="text-muted small mb-3">
        Download templates or existing data, then upload validated CSV batches for properties, units, and fees.
    </p>

    <div class="row g-3 mb-4">
        @foreach ([
            'header' => ['title' => 'Property Header', 'description' => 'Listing-level fields only. Upserts by code.'],
            'units' => ['title' => 'Property Units', 'description' => 'Unit rows linked by listing code. Upserts by code, floor, and unit.'],
            'fees' => ['title' => 'Property Fees', 'description' => 'Fee rows linked by listing code. Upserts by code and fee type.'],
        ] as $type => $definition)
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex flex-column">
                        <h2 class="h6 mb-2">{{ $definition['title'] }}</h2>
                        <p class="small text-muted flex-grow-1">{{ $definition['description'] }}</p>
                        <div class="d-flex flex-wrap gap-2">
                            @can('import', App\Modules\PropertyListings\Models\Listing::class)
                                <a href="{{ route('admin.property-uploaders.template', ['type' => $type]) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="{{ admin_icon('bi-file-earmark-spreadsheet') }} me-1"></i> Template
                                </a>
                                <a href="{{ route('admin.property-uploaders.import', ['type' => $type]) }}" class="btn btn-sm btn-primary">
                                    <i class="{{ admin_icon('bi-upload') }} me-1"></i> Upload
                                </a>
                            @endcan
                            @can('export', App\Modules\PropertyListings\Models\Listing::class)
                                <a href="{{ route('admin.property-uploaders.export', ['type' => $type] + request()->query()) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="{{ admin_icon('bi-download') }} me-1"></i> Export
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @can('batchAssets', App\Modules\PropertyListings\Models\Listing::class)
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div>
                    <h2 class="h6 mb-1">Assets Uploader</h2>
                    <p class="small text-muted mb-0">
                        Select one asset type, then upload files named <code>{code}__{whatever_text}.{ext}</code>.
                    </p>
                </div>
                <a href="{{ route('admin.property-uploaders.assets') }}" class="btn btn-primary">
                    <i class="{{ admin_icon('bi-images') }} me-1"></i> Upload Assets
                </a>
            </div>
        </div>
    @endcan
@endsection
