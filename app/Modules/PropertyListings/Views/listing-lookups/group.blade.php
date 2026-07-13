@extends('admin.layouts.app')

@php
    use App\Modules\PropertyListings\Support\ListingLookupGroups;
    $groupLabel = ListingLookupGroups::label($group);
    $showFileKind = ListingLookupGroups::usesFileKind($group);
    $showPropertyTypeProfile = ListingLookupGroups::usesPropertyTypeProfile($group);
@endphp

@section('title', $groupLabel)

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Dropdown Values', 'url' => route('admin.listing-lookups.index')],
        ['label' => $groupLabel],
    ]" />

    <x-admin.page-header
        :title="$groupLabel"
        :back-route="route('admin.listing-lookups.index')"
        back-label="All Groups"
        :create-route="route('admin.listing-lookups.create', ['group' => $groupSlug])"
        create-label="Add Value"
        :create-model="App\Modules\PropertyListings\Models\ListingLookup::class"
    />

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-borderless table-striped table-hover align-middle mb-0 admin-list-table">
                <thead>
                    <tr>
                        <th>Value</th>
                        <th>Label</th>
                        @if ($showPropertyTypeProfile)
                            <th>Summary</th>
                        @endif
                        @if ($showFileKind)
                            <th>File Kind</th>
                        @endif
                        <th>Sort</th>
                        <th>Active</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lookups as $lookup)
                        <tr>
                            <td><code>{{ $lookup->value }}</code></td>
                            <td>{{ $lookup->label }}</td>
                            @if ($showPropertyTypeProfile)
                                <td class="small text-muted">{{ $lookup->summary ?: '—' }}</td>
                            @endif
                            @if ($showFileKind)
                                <td>{{ $lookup->meta['file_kind'] ?? '—' }}</td>
                            @endif
                            <td>{{ $lookup->sort_order }}</td>
                            <td>
                                @if ($lookup->is_active)
                                    <span class="badge bg-success-subtle text-success">Active</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                @can('update', $lookup)
                                    <a href="{{ route('admin.listing-lookups.edit', ['group' => $groupSlug, 'listing_lookup' => $lookup]) }}"
                                       class="btn admin-icon-btn"
                                       title="Edit"
                                       aria-label="Edit {{ $lookup->label }}">
                                        <i class="{{ admin_icon('bi-pencil') }}" aria-hidden="true"></i>
                                    </a>
                                @endcan
                                @can('delete', $lookup)
                                    <form method="POST"
                                          action="{{ route('admin.listing-lookups.destroy', ['group' => $groupSlug, 'listing_lookup' => $lookup]) }}"
                                          class="d-inline"
                                          onsubmit="return confirm('Delete this lookup value?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn admin-icon-btn admin-icon-btn-danger"
                                                title="Delete"
                                                aria-label="Delete {{ $lookup->label }}">
                                            <i class="{{ admin_icon('bi-trash') }}" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <x-admin.empty-state :colspan="($showFileKind ? 1 : 0) + ($showPropertyTypeProfile ? 1 : 0) + 5" message="No values in this group yet." />
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
