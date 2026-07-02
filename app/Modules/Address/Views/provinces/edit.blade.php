@extends('admin.layouts.app')

@section('title', 'Edit Province')

@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Provinces', 'url' => route('admin.provinces.index')],
        ['label' => $province->name],
    ];
@endphp

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Edit Province</h1>
        <a href="{{ route('admin.provinces.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.provinces.update', $province) }}">
                        @csrf
                        @method('PUT')
                        @include('address::provinces._form', ['province' => $province])
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
