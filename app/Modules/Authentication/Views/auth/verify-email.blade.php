@extends('admin.layouts.auth')

@section('title', 'Verify Email')

@section('content')
    <div class="text-center mb-4">
        <i class="{{ admin_icon('bi-envelope-check') }} text-primary" style="font-size: 3rem;"></i>
    </div>

    <h2 class="h5 text-center mb-2">Verify Your Email</h2>
    <p class="text-muted text-center small mb-4">
        Thanks for signing up! Before getting started, please verify your email address by clicking the link we sent you.
    </p>

    @if (session('success'))
        <div class="alert alert-success small">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.verification.send') }}">
        @csrf
        <button type="submit" class="btn btn-primary w-100 mb-3">Resend Verification Email</button>
    </form>

    <form method="POST" action="{{ route('admin.logout') }}">
        @csrf
        <button type="submit" class="btn btn-outline-secondary w-100">Sign Out</button>
    </form>
@endsection
