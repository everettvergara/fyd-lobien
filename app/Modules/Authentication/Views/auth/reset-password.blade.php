@extends('admin.layouts.auth')

@section('title', 'Reset Password')

@section('content')
    <h2 class="h5 text-center mb-4">Reset Password</h2>

    <form method="POST" action="{{ route('admin.password.update', absolute: false) }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email"
                   class="form-control @error('email') is-invalid @enderror"
                   id="email"
                   name="email"
                   value="{{ old('email', $email) }}"
                   required
                   autofocus
                   autocomplete="username">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <x-admin.form.password label="New Password" name="password" required autocomplete="new-password" />

        <x-admin.form.password label="Confirm New Password" name="password_confirmation" class="mb-4" required autocomplete="new-password" />

        <button type="submit" class="btn btn-primary w-100 mb-3">Reset Password</button>

        <p class="text-center text-muted small mb-0">
            <a href="{{ route('admin.login') }}" class="text-decoration-none">Back to Sign In</a>
        </p>
    </form>
@endsection
