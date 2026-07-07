@extends('admin.layouts.app')

@section('title', 'Search Banners')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Listings', 'url' => route('admin.listings.index')],
        ['label' => 'Search Banners'],
    ]" />

    <x-admin.page-header
        title="Search Banners"
        :create-route="route('admin.property-search-banners.create')"
        create-label="Add Search Banner"
        :create-model="App\Modules\PropertyListings\Models\PropertySearchBanner::class"
    />

    <p class="text-muted mb-4">
        Configure full-width property search banners here. Page Manager blocks only pick a banner by key —
        image and heading are edited in this section, not on individual pages.
    </p>

    <x-admin.list.index
        :result="$list"
        :reset-route="route('admin.property-search-banners.index')"
    />
@endsection
