@extends('admin.layouts.app')

@section('title', $user->name)

@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Users', 'url' => route('admin.users.index')],
        ['label' => $user->name],
    ];
@endphp

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">{{ $user->name }}</h1>
        <div class="d-flex gap-2">
            @can('update', $user)
                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
            @endcan
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-muted">Name</dt>
                        <dd class="col-sm-8">{{ $user->name }}</dd>

                        <dt class="col-sm-4 text-muted">Email</dt>
                        <dd class="col-sm-8">{{ $user->email }}</dd>

                        <dt class="col-sm-4 text-muted">Status</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-primary-subtle text-primary">{{ $user->status->label() }}</span>
                        </dd>

                        <dt class="col-sm-4 text-muted">Roles</dt>
                        <dd class="col-sm-8">
                            @forelse ($user->roles as $role)
                                <span class="badge bg-secondary-subtle text-secondary me-1">{{ $role->display_name }}</span>
                            @empty
                                <span class="text-muted">No roles assigned</span>
                            @endforelse
                        </dd>

                        <dt class="col-sm-4 text-muted">Last Login</dt>
                        <dd class="col-sm-8">
                            {{ $user->last_login_at ? $user->last_login_at->format('M j, Y g:i A') : 'Never' }}
                        </dd>

                        <dt class="col-sm-4 text-muted">Member Since</dt>
                        <dd class="col-sm-8">{{ $user->created_at->format('M j, Y') }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection
