@extends('admin.layouts.app')

@section('title', 'Edit City')

@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Cities', 'url' => route('admin.cities.index')],
        ['label' => $city->name],
    ];
@endphp

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Edit City</h1>
        <a href="{{ route('admin.cities.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.cities.update', $city) }}">
                        @csrf
                        @method('PUT')
                        @include('address::cities._form', ['city' => $city])
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
