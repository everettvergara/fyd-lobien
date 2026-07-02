@extends('admin.layouts.app')

@section('title', $user->name)

@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Users', 'url' => route('admin.users.index')],
        ['label' => $user->name],
    ];
@endphp

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">{{ $user->name }}</h1>
        <div class="d-flex gap-2">
            @can('update', $user)
                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
                    <i class="{{ admin_icon('bi-pencil') }} me-1"></i> Edit
                </a>
            @endcan
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Back</a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex flex-column flex-sm-row align-items-center align-items-sm-start gap-4">
                        @if ($user->avatarUrl())
                            <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}" class="rounded-circle flex-shrink-0" width="120" height="120" style="object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center flex-shrink-0" style="width:120px;height:120px;">
                                <i class="{{ admin_icon('bi-person-circle') }} text-muted" style="font-size:4rem;"></i>
                            </div>
                        @endif
                        <div class="text-center text-sm-start">
                            <h2 class="h4 mb-1">{{ $user->name }}</h2>
                            @if ($user->province || $user->city)
                                <p class="text-muted mb-2">{{ collect([$user->province?->name, $user->city?->name])->filter()->join(', ') }}</p>
                            @endif
                            @if (auth()->id() === $user->id)
                                <a href="{{ route('admin.profile.edit') }}" class="btn btn-outline-primary">Edit My Profile</a>
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
                        <dd class="col-sm-8">{{ $user->email }}</dd>

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

                        <dt class="col-sm-4 text-muted">Roles</dt>
                        <dd class="col-sm-8">
                            @forelse ($user->roles as $role)
                                <span class="badge bg-secondary-subtle text-secondary me-1">{{ $role->display_name }}</span>
                            @empty
                                <span class="text-muted">No roles assigned</span>
                            @endforelse
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
