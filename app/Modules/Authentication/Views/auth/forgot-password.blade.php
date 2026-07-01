@extends('admin.layouts.auth')

@section('title', 'Forgot Password')

@section('content')
    <h2 class="h5 fw-semibold text-center mb-2">Forgot Password</h2>
    <p class="text-muted text-center small mb-4">Enter your email address and we will send you a reset link.</p>

    <form method="POST" action="{{ route('admin.password.email') }}">
        @csrf

        <div class="mb-4">
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

        <button type="submit" class="btn btn-primary w-100 mb-3">Send Reset Link</button>

        <p class="text-center text-muted small mb-0">
            <a href="{{ route('admin.login') }}" class="text-decoration-none">Back to Sign In</a>
        </p>
    </form>
@endsection
