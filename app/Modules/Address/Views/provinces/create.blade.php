@extends('admin.layouts.app')

@section('title', 'Create Province')

@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Provinces', 'url' => route('admin.provinces.index')],
        ['label' => 'Create'],
    ];
@endphp

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Create Province</h1>
        <a href="{{ route('admin.provinces.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.provinces.store') }}">
                        @csrf
                        @include('address::provinces._form', ['province' => null])
                        <button type="submit" class="btn btn-primary">Create Province</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
