@extends('admin.layouts.app')

@section('title', $province->name)

@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Provinces', 'url' => route('admin.provinces.index')],
        ['label' => $province->name],
    ];
@endphp

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">{{ $province->name }}</h1>
        <div class="d-flex gap-2">
            @can('update', $province)
                <a href="{{ route('admin.provinces.edit', $province) }}" class="btn btn-primary">
                    <i class="{{ admin_icon('bi-pencil') }} me-1"></i> Edit
                </a>
            @endcan
            <a href="{{ route('admin.provinces.index') }}" class="btn btn-outline-secondary">Back</a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-muted">Name</dt>
                        <dd class="col-sm-8">{{ $province->name }}</dd>

                        @if ($province->image)
                            <dt class="col-sm-4 text-muted">Image</dt>
                            <dd class="col-sm-8">
                                <img src="{{ $province->image->url() }}" alt="{{ $province->name }}" class="img-fluid rounded border" style="max-width:240px;">
                            </dd>
                        @endif

                        @if ($province->summary)
                            <dt class="col-sm-4 text-muted">Summary</dt>
                            <dd class="col-sm-8">{{ $province->summary }}</dd>
                        @endif

                        @if ($province->description)
                            <dt class="col-sm-4 text-muted">Description</dt>
                            <dd class="col-sm-8">{!! $province->description !!}</dd>
                        @endif

                        <dt class="col-sm-4 text-muted">Code</dt>
                        <dd class="col-sm-8">{{ $province->code ?? '—' }}</dd>

                        <dt class="col-sm-4 text-muted">Status</dt>
                        <dd class="col-sm-8">
                            @if ($province->is_active)
                                <span class="badge bg-success-subtle text-success">Active</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4 text-muted">Cities</dt>
                        <dd class="col-sm-8">{{ $province->cities_count }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection
