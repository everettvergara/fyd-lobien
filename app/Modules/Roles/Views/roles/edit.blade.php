@extends('admin.layouts.app')

@section('title', 'Edit Role')

@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Roles', 'url' => route('admin.roles.index')],
        ['label' => $role->display_name],
        ['label' => 'Edit'],
    ];
@endphp

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Edit Role</h1>
        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
    </div>

    <div class="row">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.roles.update', $role) }}">
                        @csrf
                        @method('PUT')
                        @include('roles::roles._form', ['role' => $role])
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
