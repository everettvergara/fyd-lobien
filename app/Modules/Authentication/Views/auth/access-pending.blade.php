@extends('admin.layouts.auth')

@section('title', 'Access Pending')

@section('content')
    <div class="text-center mb-4">
        <i class="{{ admin_icon('bi-hourglass-split') }} text-primary" style="font-size: 3rem;"></i>
    </div>

    <h2 class="h5 text-center mb-2">Access Pending</h2>
    <p class="text-muted text-center small mb-4">
        Your account is active, but an administrator has not assigned you a role yet.
        Please contact an administrator to request access.
    </p>

    <form method="POST" action="{{ route('admin.logout') }}">
        @csrf
        <button type="submit" class="btn btn-outline-secondary w-100">Sign Out</button>
    </form>
@endsection
