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
            <a href="{{ route('admin.profile.edit') }}" class="btn btn-primary">
                <i class="{{ admin_icon('bi-pencil') }} me-1"></i> Edit Profile
            </a>
            <a href="{{ route('admin.password.change') }}" class="btn btn-outline-secondary">
                <i class="{{ admin_icon('bi-key') }} me-1"></i> Change Password
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex flex-column flex-sm-row align-items-center align-items-sm-start gap-4">
                        @if ($user->avatarUrl())
                            <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}" class="rounded-circle flex-shrink-0" width="140" height="140" style="object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center flex-shrink-0" style="width:140px;height:140px;">
                                <i class="{{ admin_icon('bi-person-circle') }} text-muted" style="font-size:5rem;"></i>
                            </div>
                        @endif
                        <div class="text-center text-sm-start">
                            <h2 class="h3 mb-1">{{ $user->name }}</h2>
                            @if ($user->province || $user->city)
                                <p class="text-muted mb-0">{{ collect([$user->province?->name, $user->city?->name])->filter()->join(', ') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if ($user->about_me)
                <div class="card mb-4">
                    <div class="card-body">
                        <h2 class="h6 text-muted text-uppercase mb-3">About Me</h2>
                        <div class="lh-lg text-body-secondary">{!! nl2br(e($user->about_me)) !!}</div>
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-muted">Email</dt>
                        <dd class="col-sm-8">
                            {{ $user->email }}
                            @if ($user->hasVerifiedEmail())
                                <span class="badge bg-success-subtle text-success ms-1">Verified</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning ms-1">Unverified</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4 text-muted">Contact Number</dt>
                        <dd class="col-sm-8">{{ $user->contact_number ?: '—' }}</dd>

                        <dt class="col-sm-4 text-muted">Province</dt>
                        <dd class="col-sm-8">{{ $user->province?->name ?? '—' }}</dd>

                        <dt class="col-sm-4 text-muted">City</dt>
                        <dd class="col-sm-8">{{ $user->city?->name ?? '—' }}</dd>

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
