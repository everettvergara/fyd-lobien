@extends('admin.layouts.app')

@section('title', 'Menus')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Menus'],
    ]" />

    <x-admin.page-header
        title="Menus"
        :create-route="route('admin.menus.create')"
        create-label="Add Menu"
        :create-model="App\Modules\Menus\Models\Menu::class"
    />

    <x-admin.card :padding="false">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Location</th>
                        <th>Items</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($menus as $menu)
                        <tr>
                            <td class="fw-medium">{{ $menu->name }}</td>
                            <td><x-admin.status-badge :status="$menu->location" variant="secondary" /></td>
                            <td>{{ $menu->all_items_count }}</td>
                            <td class="text-end">
                                <x-admin.table-actions>
                                    @can('update', $menu)
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.menus.edit', $menu) }}">
                                                <i class="bi bi-pencil me-2"></i>Edit
                                            </a>
                                        </li>
                                    @endcan
                                    @can('delete', $menu)
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="{{ route('admin.menus.destroy', $menu) }}" onsubmit="return confirm('Delete this menu?')">
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
                        <x-admin.empty-state :colspan="4" message="No menus found." />
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>
@endsection
