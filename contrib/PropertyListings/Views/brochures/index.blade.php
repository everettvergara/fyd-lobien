@extends('admin.layouts.app')

@section('title', 'Brochures — '.$listing->name)

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Listings', 'url' => route('admin.listings.index')],
        ['label' => $listing->name, 'url' => route('admin.listings.edit', $listing)],
        ['label' => 'Brochures'],
    ]" />

    <x-admin.page-header
        :title="'Brochures — '.$listing->code"
        :back-route="route('admin.listings.edit', $listing)"
        back-label="Back to Listing"
    />

    <div class="row g-3">
        @foreach ($types as $type => $definition)
            <div class="col-md-4 col-lg-3">
                <div class="card border-0 shadow-sm listing-brochure-hub-card h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="mb-3">
                            <i class="{{ admin_icon($definition['icon']) }} fs-3 text-primary" aria-hidden="true"></i>
                        </div>
                        <h2 class="h6 mb-3">{{ $definition['label'] }}</h2>
                        <a href="{{ \App\Modules\PropertyListings\Support\ListingBrochureTypes::url($listing, $type) }}"
                           class="btn btn-outline-primary btn-sm mt-auto"
                           target="_blank"
                           rel="noopener">
                            Open Preview
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection

@push('styles')
    @include('propertylistings::brochures._styles')
@endpush
