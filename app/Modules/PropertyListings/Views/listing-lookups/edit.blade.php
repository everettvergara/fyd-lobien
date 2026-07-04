@extends('admin.layouts.app')

@php
    $groupLabel = \App\Modules\PropertyListings\Support\ListingLookupGroups::label($lookup->group);
@endphp

@section('title', 'Edit ' . $lookup->label)

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Dropdown Values', 'url' => route('admin.listing-lookups.index')],
        ['label' => $groupLabel, 'url' => route('admin.listing-lookups.group', $groupSlug)],
        ['label' => $lookup->label],
    ]" />

    <x-admin.page-header title="Edit Lookup Value — {{ $lookup->label }}" />

    <form method="POST" action="{{ route('admin.listing-lookups.update', ['group' => $groupSlug, 'listing_lookup' => $lookup]) }}" class="card border-0 shadow-sm">
        @csrf
        @method('PUT')
        <div class="card-body">
            @include('propertylistings::listing-lookups._form', ['lookup' => $lookup])
        </div>
        <div class="card-footer bg-white border-top d-flex gap-2">
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('admin.listing-lookups.group', $groupSlug) }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
@endsection
