@extends('admin.layouts.auth')

@section('title', 'Sign In')

@section('content')
    <h2 class="h5 fw-semibold text-center mb-4">Sign In</h2>

    <form method="POST" action="{{ route('admin.login') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email"
                   class="form-control @error('email') is-invalid @enderror"
                   id="email"
                   name="email"
                   value="{{ old('email') }}"
                   required
                   autofocus
                   autocomplete="username">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password"
                   class="form-control @error('password') is-invalid @enderror"
                   id="password"
                   name="password"
                   required
                   autocomplete="current-password">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                <label class="form-check-label" for="remember">Remember me</label>
            </div>
            <a href="{{ route('admin.password.request') }}" class="small text-decoration-none">Forgot password?</a>
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-3">Sign In</button>

        @if (config('fyd.registration_enabled', true))
            <p class="text-center text-muted small mb-0">
                Don't have an account?
                <a href="{{ route('admin.register') }}" class="text-decoration-none">Register</a>
            </p>
        @endif
    </form>
@endsection
