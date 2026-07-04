@extends('admin.layouts.app')

@section('title', 'Property Listings Configuration')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Listings', 'url' => route('admin.listings.index')],
        ['label' => 'Configuration'],
    ]" />

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Property Listings Configuration</h1>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header py-2 small">Sample Data</div>
        <div class="card-body">
            <p class="text-muted mb-3">
                Dropdown values are seeded automatically when the module is installed.
                Property listings are <strong>not</strong> created on install — use the button below
                to add or refresh five demo listings (<code>DEMO-001</code> … <code>DEMO-005</code>)
                with full form data, units, fees, remarks, and image assets.
            </p>

            <p class="small text-muted mb-3">
                Current demo listings: <strong>{{ $demoCount }}</strong>
            </p>

            @if (! ($gdAvailable ?? true))
                <div class="alert alert-warning small py-2">
                    PHP GD is not enabled. Sample assets will use a plain fallback image instead of labeled demo graphics.
                    Enable the <code>gd</code> extension for richer demo thumbnails.
                </div>
            @endif

            @can('manage', App\Modules\PropertyListings\Models\ListingConfiguration::class)
                <form method="POST"
                      action="{{ route('admin.listings.configuration.seed-samples') }}"
                      onsubmit="return confirm('Create or refresh 5 demo property listings (DEMO-001 … DEMO-005)?');">
                    @csrf
                    <button type="submit" class="btn btn-primary">Seed Sample Listings</button>
                </form>
            @endcan
        </div>
    </div>
@endsection
