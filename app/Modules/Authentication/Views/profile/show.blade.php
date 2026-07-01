@extends('admin.layouts.app')

@section('title', 'Profile')

@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Profile'],
    ];
@endphp

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Profile</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.profile.edit') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-pencil me-1"></i> Edit Profile
            </a>
            <a href="{{ route('admin.password.change') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-key me-1"></i> Change Password
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-muted">Name</dt>
                        <dd class="col-sm-8">{{ $user->name }}</dd>

                        <dt class="col-sm-4 text-muted">Email</dt>
                        <dd class="col-sm-8">
                            {{ $user->email }}
                            @if ($user->hasVerifiedEmail())
                                <span class="badge bg-success-subtle text-success ms-1">Verified</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning ms-1">Unverified</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4 text-muted">Status</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-primary-subtle text-primary">{{ $user->status->label() }}</span>
                        </dd>

                        <dt class="col-sm-4 text-muted">Last Login</dt>
                        <dd class="col-sm-8">
                            {{ $user->last_login_at ? $user->last_login_at->format('M j, Y g:i A') : 'Never' }}
                        </dd>

                        <dt class="col-sm-4 text-muted">Member Since</dt>
                        <dd class="col-sm-8">{{ $user->created_at->format('M j, Y') }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection
