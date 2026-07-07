@extends('admin.layouts.app')

@section('title', 'Create Search Banner')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Listings', 'url' => route('admin.listings.index')],
        ['label' => 'Search Banners', 'url' => route('admin.property-search-banners.index')],
        ['label' => 'Create'],
    ]" />

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Create Search Banner</h1>
        <a href="{{ route('admin.property-search-banners.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.property-search-banners.store') }}">
                        @csrf
                        @include('propertylistings::property-search-banners._form', ['banner' => null])
                        <button type="submit" class="btn btn-primary">Create Search Banner</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
