@extends('admin.layouts.app')

@section('title', 'Referring Sites')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Referring Sites'],
    ]" />

    <x-admin.page-header title="Referring Sites" />

    <div class="row g-2 mb-3">
        <div class="col-sm-4">
            <div class="card admin-stat-card h-100">
                <div class="card-body">
                    <p class="text-muted small mb-1">Total hits</p>
                    <h3 class="h5 mb-0">{{ number_format($summary['total_hits']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card admin-stat-card h-100">
                <div class="card-body">
                    <p class="text-muted small mb-1">Referred hits</p>
                    <h3 class="h5 mb-0">{{ number_format($summary['referred_hits']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card admin-stat-card h-100">
                <div class="card-body">
                    <p class="text-muted small mb-1">Direct hits</p>
                    <h3 class="h5 mb-0">{{ number_format($summary['direct_hits']) }}</h3>
                </div>
            </div>
        </div>
    </div>

    @if ($summary['total_hits'] > 0 && $summary['referred_hits'] === 0)
        <div class="alert alert-info small mb-3" role="status">
            Direct visits and same-site navigation are counted in the summary cards above but are not listed in the table below.
            Only external referring sites appear here.
        </div>
    @endif

    <x-admin.list.index
        :result="$list"
        :reset-route="route('admin.site-reports.referrers.index')"
    />
@endsection
