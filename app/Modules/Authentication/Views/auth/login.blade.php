@extends('admin.layouts.auth')

@section('title', 'Sign In')

@section('content')
    <h2 class="h5 text-center mb-4">Sign In</h2>

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

        <x-admin.form.password label="Password" name="password" required autocomplete="current-password" />

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                <label class="form-check-label" for="remember">Remember me</label>
            </div>
            <a href="{{ route('admin.password.request') }}" class="small text-decoration-none">Forgot password?</a>
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-3">Sign In</button>

        @if (app(\App\Services\AuthConfigService::class)->registrationEnabled())
            <p class="text-center text-muted small mb-0">
                Don't have an account?
                <a href="{{ route('admin.register') }}" class="text-decoration-none">Register</a>
            </p>
        @endif
    </form>
@endsection
