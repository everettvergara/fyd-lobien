@extends('admin.layouts.app')

@section('title', 'Banners')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Banners'],
    ]" />

    <x-admin.page-header
        title="Banners"
        :create-route="route('admin.banners.create')"
        create-label="Add Banner"
        :create-model="App\Modules\Banners\Models\Banner::class"
    />

    <x-admin.card :padding="false">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Placement</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($banners as $banner)
                        <tr>
                            <td class="fw-medium">{{ $banner->name }}</td>
                            <td class="small">{{ $banner->type->label() }}</td>
                            <td class="small">{{ $banner->placement->label() }}</td>
                            <td><x-admin.status-badge :status="$banner->status" /></td>
                            <td class="text-end">
                                <x-admin.table-actions>
                                    @can('update', $banner)
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.banners.edit', $banner) }}">
                                                <i class="bi bi-pencil me-2"></i>Edit
                                            </a>
                                        </li>
                                    @endcan
                                    @can('publish', $banner)
                                        @if ($banner->status !== App\Enums\ContentStatus::Published)
                                            <li>
                                                <form method="POST" action="{{ route('admin.banners.publish', $banner) }}">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="bi bi-check-circle me-2"></i>Publish
                                                    </button>
                                                </form>
                                            </li>
                                        @endif
                                    @endcan
                                    @can('create', App\Modules\Banners\Models\Banner::class)
                                        <li>
                                            <form method="POST" action="{{ route('admin.banners.duplicate', $banner) }}">
                                                @csrf
                                                <button type="submit" class="dropdown-item">
                                                    <i class="bi bi-copy me-2"></i>Duplicate
                                                </button>
                                            </form>
                                        </li>
                                    @endcan
                                    @can('delete', $banner)
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="{{ route('admin.banners.destroy', $banner) }}" onsubmit="return confirm('Delete this banner?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bi bi-trash me-2"></i>Delete
                                                </button>
                                            </form>
                                        </li>
                                    @endcan
                                </x-admin.table-actions>
                            </td>
                        </tr>
                    @empty
                        <x-admin.empty-state :colspan="5" message="No banners found." />
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($banners->hasPages())
            <x-slot:footer>{{ $banners->links() }}</x-slot:footer>
        @endif
    </x-admin.card>
@endsection
