@extends('admin.layouts.app')

@section('title', 'Change Password')

@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Profile', 'url' => route('admin.profile.show')],
        ['label' => 'Change Password'],
    ];
@endphp

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Change Password</h1>
        <a href="{{ route('admin.profile.show') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>

    <div class="row">
        <div class="col-lg-6 col-xl-5">
            <div class="card admin-compact-form-card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.password.change.update') }}">
                        @csrf
                        @method('PUT')

                        <x-admin.form.password label="Current Password" name="current_password" required autocomplete="current-password" />

                        <x-admin.form.password label="New Password" name="password" required autocomplete="new-password" />

                        <x-admin.form.password label="Confirm New Password" name="password_confirmation" class="mb-4" required autocomplete="new-password" />

                        <button type="submit" class="btn btn-primary">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
