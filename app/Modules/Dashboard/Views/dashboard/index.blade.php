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

    <div class="row g-4 mb-4">
        <div class="col-sm-6">
            <div class="card admin-stat-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="admin-stat-icon bg-secondary-subtle text-secondary">
                            <i class="bi bi-pencil-square"></i>
                        </div>
                        <div class="ms-3">
                            <p class="text-muted small mb-0">Draft Content</p>
                            <h3 class="mb-0">{{ $stats['drafts'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="card admin-stat-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="admin-stat-icon bg-success-subtle text-success">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div class="ms-3">
                            <p class="text-muted small mb-0">Published Content</p>
                            <h3 class="mb-0">{{ $stats['published'] }}</h3>
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
                    @forelse ($recentActivity as $activity)
                        <div class="d-flex align-items-start mb-3 {{ ! $loop->last ? 'border-bottom pb-3' : '' }}">
                            <div class="admin-stat-icon bg-light text-muted me-3" style="width:36px;height:36px;font-size:0.9rem;">
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <div>
                                <p class="mb-0 small">{{ $activity->description() }}</p>
                                <span class="text-muted" style="font-size:0.75rem;">{{ $activity->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No recent activity.</p>
                    @endforelse
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
