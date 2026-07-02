@extends('admin.layouts.auth')

@section('title', 'Register')

@section('content')
    <h2 class="h5 text-center mb-4">Create Account</h2>

    <form method="POST" action="{{ route('admin.register') }}">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Full Name</label>
            <input type="text"
                   class="form-control @error('name') is-invalid @enderror"
                   id="name"
                   name="name"
                   value="{{ old('name') }}"
                   required
                   autofocus
                   autocomplete="name">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email"
                   class="form-control @error('email') is-invalid @enderror"
                   id="email"
                   name="email"
                   value="{{ old('email') }}"
                   required
                   autocomplete="username">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <x-admin.form.password label="Password" name="password" required autocomplete="new-password" />

        <x-admin.form.password label="Confirm Password" name="password_confirmation" class="mb-4" required autocomplete="new-password" />

        <button type="submit" class="btn btn-primary w-100 mb-3">Register</button>

        <p class="text-center text-muted small mb-0">
            Already have an account?
            <a href="{{ route('admin.login') }}" class="text-decoration-none">Sign In</a>
        </p>
    </form>
@endsection
