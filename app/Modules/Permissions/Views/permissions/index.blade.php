@extends('admin.layouts.app')

@section('title', 'Permissions')

@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Permissions'],
    ];
@endphp

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Permissions</h1>
    </div>

    <p class="text-muted mb-4">System-defined permissions grouped by module. Permissions are assigned to roles.</p>

    <div class="row g-4">
        @foreach ($permissions as $module => $modulePermissions)
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0 text-capitalize">{{ $module }}</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            @foreach ($modulePermissions as $permission)
                                <li class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="small">{{ $permission->display_name }}</span>
                                    <code class="small text-muted">{{ $permission->name }}</code>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
