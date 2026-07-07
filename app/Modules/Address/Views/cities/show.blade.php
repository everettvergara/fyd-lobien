@extends('admin.layouts.app')

@section('title', $city->name)

@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Cities', 'url' => route('admin.cities.index')],
        ['label' => $city->name],
    ];
@endphp

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">{{ $city->name }}</h1>
        <div class="d-flex gap-2">
            @can('update', $city)
                <a href="{{ route('admin.cities.edit', $city) }}" class="btn btn-primary">
                    <i class="{{ admin_icon('bi-pencil') }} me-1"></i> Edit
                </a>
            @endcan
            <a href="{{ route('admin.cities.index') }}" class="btn btn-outline-secondary">Back</a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-muted">Name</dt>
                        <dd class="col-sm-8">{{ $city->name }}</dd>

                        @if ($city->image)
                            <dt class="col-sm-4 text-muted">Image</dt>
                            <dd class="col-sm-8">
                                <img src="{{ $city->image->url() }}" alt="{{ $city->name }}" class="img-fluid rounded border" style="max-width:240px;">
                            </dd>
                        @endif

                        @if ($city->summary)
                            <dt class="col-sm-4 text-muted">Summary</dt>
                            <dd class="col-sm-8">{{ $city->summary }}</dd>
                        @endif

                        @if ($city->description)
                            <dt class="col-sm-4 text-muted">Description</dt>
                            <dd class="col-sm-8">{!! $city->description !!}</dd>
                        @endif

                        <dt class="col-sm-4 text-muted">Province</dt>
                        <dd class="col-sm-8">
                            <a href="{{ route('admin.provinces.show', $city->province) }}" class="text-decoration-none">
                                {{ $city->province->name }}
                            </a>
                        </dd>

                        <dt class="col-sm-4 text-muted">Status</dt>
                        <dd class="col-sm-8">
                            @if ($city->is_active)
                                <span class="badge bg-success-subtle text-success">Active</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection
