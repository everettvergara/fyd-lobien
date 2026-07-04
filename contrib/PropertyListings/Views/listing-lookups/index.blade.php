@extends('admin.layouts.app')

@section('title', 'Dropdown Values')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Dropdown Values'],
    ]" />

    <x-admin.page-header title="Dropdown Values" />

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-borderless table-striped table-hover align-middle mb-0 admin-list-table">
                <thead>
                    <tr>
                        <th>Group</th>
                        <th>Label</th>
                        <th class="text-end">Values</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (\App\Modules\PropertyListings\Support\ListingLookupGroups::labels() as $groupKey => $groupLabel)
                        @php
                            $count = $groupCounts[$groupKey] ?? 0;
                            $slug = $groupSlugs[$groupKey] ?? $groupKey;
                        @endphp
                        <tr>
                            <td><code>{{ $groupKey }}</code></td>
                            <td>{{ $groupLabel }}</td>
                            <td class="text-end">{{ number_format($count) }}</td>
                            <td class="text-end text-nowrap">
                                @can('viewAny', App\Modules\PropertyListings\Models\ListingLookup::class)
                                    <a href="{{ route('admin.listing-lookups.group', $slug) }}"
                                       class="btn admin-icon-btn"
                                       title="Manage values"
                                       aria-label="Manage {{ $groupLabel }}">
                                        <i class="{{ admin_icon('bi-menu-button-wide') }}" aria-hidden="true"></i>
                                    </a>
                                @endcan
                                @can('create', App\Modules\PropertyListings\Models\ListingLookup::class)
                                    <a href="{{ route('admin.listing-lookups.create', ['group' => $slug]) }}"
                                       class="btn admin-icon-btn"
                                       title="Add value"
                                       aria-label="Add {{ $groupLabel }} value">
                                        <i class="{{ admin_icon('bi-plus-lg') }}" aria-hidden="true"></i>
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
