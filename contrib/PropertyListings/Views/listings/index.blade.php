@extends('admin.layouts.app')

@php
    $viewMode = request('view', 'table');
@endphp

@section('title', 'Listings')

@section('content')
    <div class="listing-index-page pb-5" data-listing-index data-listings-view="{{ $viewMode }}">
        <x-admin.breadcrumbs :items="[
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Listings'],
        ]" />

        <x-admin.page-header
            title="Listings"
            :create-route="route('admin.listings.create')"
            create-label="Add Listing"
            :create-model="App\Modules\PropertyListings\Models\Listing::class"
        >
            <x-slot:actions>
                <div class="btn-group" role="group" aria-label="View mode">
                    <button type="button"
                            class="btn btn-outline-secondary @if ($viewMode !== 'thumbnails') active @endif"
                            data-listings-view-toggle="table"
                            title="Table view">
                        <i class="{{ admin_icon('bi-table') }}" aria-hidden="true"></i>
                    </button>
                    <button type="button"
                            class="btn btn-outline-secondary @if ($viewMode === 'thumbnails') active @endif"
                            data-listings-view-toggle="thumbnails"
                            title="Thumbnail view">
                        <i class="{{ admin_icon('bi-grid-3x3-gap') }}" aria-hidden="true"></i>
                    </button>
                </div>

                @can('export', App\Modules\PropertyListings\Models\Listing::class)
                    <a href="{{ route('admin.listings.export', request()->query()) }}" class="btn btn-outline-secondary">
                        <i class="{{ admin_icon('bi-download') }} me-1"></i> Download CSV
                    </a>
                @endcan

                @can('import', App\Modules\PropertyListings\Models\Listing::class)
                    <a href="{{ route('admin.listings.import.template') }}" class="btn btn-outline-secondary">
                        <i class="{{ admin_icon('bi-file-earmark-spreadsheet') }} me-1"></i> Template
                    </a>
                    <a href="{{ route('admin.listings.import') }}" class="btn btn-outline-secondary">
                        <i class="{{ admin_icon('bi-upload') }} me-1"></i> Upload CSV
                    </a>
                @endcan

                @can('batchAssets', App\Modules\PropertyListings\Models\Listing::class)
                    <a href="{{ route('admin.listings.assets.batch') }}" class="btn btn-outline-secondary">
                        <i class="{{ admin_icon('bi-images') }} me-1"></i> Batch Assets
                    </a>
                @endcan
            </x-slot:actions>
        </x-admin.page-header>

        @php
            $resetRoute = route('admin.listings.index', array_filter([
                'view' => $viewMode !== 'table' ? $viewMode : null,
            ]));
        @endphp

        <x-admin.card :padding="false" class="admin-list-card mb-4">
            @include('propertylistings::listings._filters', [
                'result' => $list,
                'resetRoute' => $resetRoute,
            ])
        </x-admin.card>

        @if ($viewMode === 'thumbnails')
            <div class="row g-3 mb-4" data-listings-thumbnail-grid>
                @forelse ($list->records as $listing)
                    <div class="col-sm-6 col-md-4 col-xl-3">
                        @include('propertylistings::listings._thumbnail-card', ['listing' => $listing])
                    </div>
                @empty
                    <div class="col-12">
                        <x-admin.empty-state message="No listings found." />
                    </div>
                @endforelse
            </div>

            @if ($list->records->hasPages())
                <x-admin.card :padding="false" class="admin-list-card mb-5">
                    <x-slot:footer>
                        <x-admin.list.pagination :result="$list" />
                    </x-slot:footer>
                </x-admin.card>
            @endif
        @else
            <x-admin.card :padding="false" class="admin-list-card mb-5">
                <x-admin.list.table :result="$list" />

                @if ($list->records->hasPages())
                    <x-slot:footer>
                        <x-admin.list.pagination :result="$list" />
                    </x-slot:footer>
                @endif
            </x-admin.card>
        @endif
    </div>

    <div class="listing-comparator-bin border-top bg-white shadow-sm position-fixed bottom-0 start-0 end-0 d-none"
         data-listing-comparator-bin
         data-compare-url="{{ route('admin.listings.compare') }}"
         style="z-index:1030;">
        <div class="container-fluid py-2">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <button type="button"
                        class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1"
                        data-listing-comparator-open
                        title="Compare selected listings">
                    <i class="{{ admin_icon('bi-intersect') }}" aria-hidden="true"></i>
                    <span>Compare</span>
                    <span class="badge bg-primary rounded-pill" data-listing-comparator-count>0</span>
                </button>
                <div class="d-flex flex-wrap gap-1 flex-grow-1" data-listing-comparator-chips></div>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-listing-comparator-clear>
                    Clear all
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const VIEW_KEY = 'listings_view';
    const page = document.querySelector('[data-listing-index]');
    const currentView = page?.dataset.listingsView || 'table';
    const storedView = sessionStorage.getItem(VIEW_KEY);

    if (!new URLSearchParams(window.location.search).has('view') && storedView && storedView !== currentView) {
        const url = new URL(window.location.href);
        url.searchParams.set('view', storedView);
        window.location.replace(url.toString());
        return;
    }

    sessionStorage.setItem(VIEW_KEY, currentView);

    document.querySelectorAll('[data-listings-view-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const view = button.dataset.listingsViewToggle;
            sessionStorage.setItem(VIEW_KEY, view);
            const url = new URL(window.location.href);
            url.searchParams.set('view', view);
            window.location.href = url.toString();
        });
    });
});
</script>
@endpush
