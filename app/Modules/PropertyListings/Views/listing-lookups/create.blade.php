@extends('admin.layouts.app')

@php
    $groupLabel = \App\Modules\PropertyListings\Support\ListingLookupGroups::label($group);
@endphp

@section('title', 'Add ' . $groupLabel)

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Dropdown Values', 'url' => route('admin.listing-lookups.index')],
        ['label' => $groupLabel, 'url' => route('admin.listing-lookups.group', $groupSlug)],
        ['label' => 'Add'],
    ]" />

    <x-admin.page-header title="Add {{ $groupLabel }} Value" />

    <form method="POST" action="{{ route('admin.listing-lookups.store', ['group' => $groupSlug]) }}" class="card border-0 shadow-sm">
        @csrf
        <div class="card-body">
            @include('propertylistings::listing-lookups._form', ['group' => $group])
        </div>
        <div class="card-footer bg-white border-top d-flex gap-2">
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('admin.listing-lookups.group', $groupSlug) }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
@endsection
