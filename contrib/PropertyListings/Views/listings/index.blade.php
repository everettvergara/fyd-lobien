@extends('admin.layouts.app')

@php
    $viewMode = request('view', 'table');
@endphp

@section('title', 'Listings')

@section('content')
    <div class="listing-index-page" data-listing-index data-listings-view="{{ $viewMode }}">
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

            </x-slot:actions>
        </x-admin.page-header>

        @php
            $resetRoute = route('admin.listings.index', array_filter([
                'view' => $viewMode !== 'table' ? $viewMode : null,
            ]));
        @endphp

        @if ($viewMode === 'thumbnails')
            <x-admin.card :padding="false" class="admin-list-card mb-4">
                @include('propertylistings::listings._filters', [
                    'result' => $list,
                    'resetRoute' => $resetRoute,
                ])
            </x-admin.card>

            @include('propertylistings::listings._compare-bin')

            <div class="row g-2 mb-4" data-listings-thumbnail-grid>
                @forelse ($list->records as $listing)
                    <div class="listing-thumbnail-grid-item">
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
                @include('propertylistings::listings._filters', [
                    'result' => $list,
                    'resetRoute' => $resetRoute,
                ])

                @include('propertylistings::listings._compare-bin')

                @include('propertylistings::listings._table', ['result' => $list])

                @if ($list->records->hasPages())
                    <x-slot:footer>
                        <x-admin.list.pagination :result="$list" />
                    </x-slot:footer>
                @endif
            </x-admin.card>
        @endif
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

@once
    @push('styles')
    <style>
        [data-listings-thumbnail-grid] {
            --bs-gutter-x: 0.5rem;
            --bs-gutter-y: 0.5rem;
        }

        [data-listings-thumbnail-grid] .listing-thumbnail-grid-item {
            flex: 0 0 75%;
            max-width: 75%;
            padding-right: calc(var(--bs-gutter-x) * 0.5);
            padding-left: calc(var(--bs-gutter-x) * 0.5);
            margin-top: var(--bs-gutter-y);
        }

        @media (min-width: 576px) {
            [data-listings-thumbnail-grid] .listing-thumbnail-grid-item {
                flex-basis: 37.5%;
                max-width: 37.5%;
            }
        }

        @media (min-width: 768px) {
            [data-listings-thumbnail-grid] .listing-thumbnail-grid-item {
                flex-basis: 25%;
                max-width: 25%;
            }
        }

        @media (min-width: 1200px) {
            [data-listings-thumbnail-grid] .listing-thumbnail-grid-item {
                flex-basis: 18.75%;
                max-width: 18.75%;
            }
        }
    </style>
    @endpush
@endonce
