@extends('admin.layouts.app')

@section('title', 'Dashboard')

@php
    $breadcrumbs = [
        ['label' => 'Dashboard'],
    ];
@endphp

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Dashboard</h1>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card admin-stat-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="admin-stat-icon bg-primary-subtle text-primary">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="ms-3">
                            <p class="text-muted small mb-0">Users</p>
                            <h3 class="mb-0">{{ $stats['users'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card admin-stat-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="admin-stat-icon bg-success-subtle text-success">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                        <div class="ms-3">
                            <p class="text-muted small mb-0">Pages</p>
                            <h3 class="mb-0">{{ $stats['pages'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card admin-stat-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="admin-stat-icon bg-info-subtle text-info">
                            <i class="bi bi-journal-text"></i>
                        </div>
                        <div class="ms-3">
                            <p class="text-muted small mb-0">Posts</p>
                            <h3 class="mb-0">{{ $stats['posts'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card admin-stat-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="admin-stat-icon bg-warning-subtle text-warning">
                            <i class="bi bi-image"></i>
                        </div>
                        <div class="ms-3">
                            <p class="text-muted small mb-0">Banners</p>
                            <h3 class="mb-0">{{ $stats['banners'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Recent Activity</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">No recent activity. Activity tracking will be available in Phase 3.</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">System Information</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-muted">CMS Version</dt>
                        <dd class="col-7">1.0.0</dd>

                        <dt class="col-5 text-muted">Laravel</dt>
                        <dd class="col-7">{{ app()->version() }}</dd>

                        <dt class="col-5 text-muted">PHP</dt>
                        <dd class="col-7">{{ PHP_VERSION }}</dd>

                        <dt class="col-5 text-muted">Environment</dt>
                        <dd class="col-7">{{ app()->environment() }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection
