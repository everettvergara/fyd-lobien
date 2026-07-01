@extends('admin.layouts.app')

@section('title', 'Roles')

@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Roles'],
    ];
@endphp

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Roles</h1>
        @can('create', App\Models\Role::class)
            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Add Role
            </a>
        @endcan
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Role</th>
                        <th>Description</th>
                        <th>Users</th>
                        <th>Permissions</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $role)
                        <tr>
                            <td>
                                <a href="{{ route('admin.roles.show', $role) }}" class="text-decoration-none fw-medium">
                                    {{ $role->display_name }}
                                </a>
                                @if ($role->is_system)
                                    <span class="badge bg-info-subtle text-info ms-1">System</span>
                                @endif
                            </td>
                            <td class="text-muted small">{{ Str::limit($role->description, 60) }}</td>
                            <td>{{ $role->users_count }}</td>
                            <td>{{ $role->permissions_count }}</td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        @can('view', $role)
                                            <li><a class="dropdown-item" href="{{ route('admin.roles.show', $role) }}"><i class="bi bi-eye me-2"></i>View</a></li>
                                        @endcan
                                        @can('update', $role)
                                            <li><a class="dropdown-item" href="{{ route('admin.roles.edit', $role) }}"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                                        @endcan
                                        @can('delete', $role)
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" onsubmit="return confirm('Are you sure?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i>Delete</button>
                                                </form>
                                            </li>
                                        @endcan
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No roles found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($roles->hasPages())
            <div class="card-footer bg-white">{{ $roles->links() }}</div>
        @endif
    </div>
@endsection
