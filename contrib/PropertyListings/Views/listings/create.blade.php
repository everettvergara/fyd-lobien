@extends('admin.layouts.app')

@section('title', 'Create Listing')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Listings', 'url' => route('admin.listings.index')],
        ['label' => 'Create'],
    ]" />

    <x-admin.page-header title="Create Listing" />

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @include('propertylistings::listings._form', [
                'lookups' => $lookups,
                'provinces' => $provinces,
                'formAction' => route('admin.listings.store'),
                'formMethod' => 'POST',
            ])
        </div>
        <div class="card-footer bg-white border-top d-flex gap-2">
            <button type="submit" form="listing-edit-form" class="btn btn-primary">Save Listing</button>
            <a href="{{ route('admin.listings.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </div>
@endsection
