@extends('admin.layouts.app')

@section('title', 'Edit Listing')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Listings', 'url' => route('admin.listings.index')],
        ['label' => $listing->name],
    ]" />

    <x-admin.page-header title="Edit Listing — {{ $listing->code }}">
        <x-slot:actions>
            @include('propertylistings::listings._brochure-shortcuts', ['listing' => $listing])
        </x-slot:actions>
    </x-admin.page-header>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @include('propertylistings::listings._form', [
                'listing' => $listing,
                'lookups' => $lookups,
                'provinces' => $provinces,
                'formAction' => route('admin.listings.update', $listing),
                'formMethod' => 'PUT',
                'remarks' => $remarks ?? null,
                'remarksUnitFilter' => $remarksUnitFilter ?? null,
            ])
        </div>
        <div class="card-footer bg-white border-top d-flex gap-2">
            <button type="submit" form="listing-edit-form" class="btn btn-primary">Save Listing</button>
            <a href="{{ route('admin.listings.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </div>
@endsection
