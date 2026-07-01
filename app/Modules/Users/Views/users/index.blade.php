@extends('admin.layouts.app')

@section('title', 'Users')

@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Users'],
    ];
@endphp

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Users</h1>
        @can('create', App\Models\User::class)
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Add User
            </a>
        @endcan
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Roles</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>
                                <a href="{{ route('admin.users.show', $user) }}" class="text-decoration-none fw-medium">
                                    {{ $user->name }}
                                </a>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @foreach ($user->roles as $role)
                                    <span class="badge bg-secondary-subtle text-secondary me-1">{{ $role->display_name }}</span>
                                @endforeach
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary">{{ $user->status->label() }}</span>
                            </td>
                            <td class="text-muted small">
                                {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}
                            </td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        @can('view', $user)
                                            <li><a class="dropdown-item" href="{{ route('admin.users.show', $user) }}"><i class="bi bi-eye me-2"></i>View</a></li>
                                        @endcan
                                        @can('update', $user)
                                            <li><a class="dropdown-item" href="{{ route('admin.users.edit', $user) }}"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            @if ($user->status !== App\Enums\UserStatus::Active)
                                                <li>
                                                    <form method="POST" action="{{ route('admin.users.activate', $user) }}">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item"><i class="bi bi-check-circle me-2"></i>Activate</button>
                                                    </form>
                                                </li>
                                            @endif
                                            @if ($user->status !== App\Enums\UserStatus::Inactive)
                                                <li>
                                                    <form method="POST" action="{{ route('admin.users.deactivate', $user) }}">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item"><i class="bi bi-x-circle me-2"></i>Deactivate</button>
                                                    </form>
                                                </li>
                                            @endif
                                            @if ($user->status !== App\Enums\UserStatus::Suspended)
                                                <li>
                                                    <form method="POST" action="{{ route('admin.users.suspend', $user) }}">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item"><i class="bi bi-slash-circle me-2"></i>Suspend</button>
                                                    </form>
                                                </li>
                                            @endif
                                            <li>
                                                <form method="POST" action="{{ route('admin.users.reset-password', $user) }}">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item"><i class="bi bi-key me-2"></i>Reset Password</button>
                                                </form>
                                            </li>
                                        @endcan
                                        @can('delete', $user)
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Are you sure you want to delete this user?')">
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
                            <td colspan="6" class="text-center text-muted py-4">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())
            <div class="card-footer bg-white">
                {{ $users->links() }}
            </div>
        @endif
    </div>
@endsection
