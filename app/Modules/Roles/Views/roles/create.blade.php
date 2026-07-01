@extends('admin.layouts.app')

@section('title', 'Create Role')

@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Roles', 'url' => route('admin.roles.index')],
        ['label' => 'Create'],
    ];
@endphp

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Create Role</h1>
        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
    </div>

    <div class="row">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.roles.store') }}">
                        @csrf
                        @include('roles::roles._form', ['role' => null])
                        <button type="submit" class="btn btn-primary">Create Role</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
