@extends('admin.layouts.app')

@section('title', $role->display_name)

@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Roles', 'url' => route('admin.roles.index')],
        ['label' => $role->display_name],
    ];
@endphp

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">{{ $role->display_name }}</h1>
        <div class="d-flex gap-2">
            @can('update', $role)
                <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
            @endcan
            <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-white"><h5 class="card-title mb-0">Details</h5></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-muted">Name</dt>
                        <dd class="col-sm-8"><code>{{ $role->name }}</code></dd>
                        <dt class="col-sm-4 text-muted">Description</dt>
                        <dd class="col-sm-8">{{ $role->description ?? '—' }}</dd>
                        <dt class="col-sm-4 text-muted">Type</dt>
                        <dd class="col-sm-8">{{ $role->is_system ? 'System Role' : 'Custom Role' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-white"><h5 class="card-title mb-0">Permissions ({{ $role->permissions->count() }})</h5></div>
                <div class="card-body">
                    @php $grouped = $role->permissions->groupBy('module'); @endphp
                    @forelse ($grouped as $module => $perms)
                        <h6 class="text-uppercase text-muted small">{{ ucfirst($module) }}</h6>
                        <div class="mb-3">
                            @foreach ($perms as $perm)
                                <span class="badge bg-secondary-subtle text-secondary me-1 mb-1">{{ $perm->display_name }}</span>
                            @endforeach
                        </div>
                    @empty
                        <p class="text-muted mb-0">No permissions assigned.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
