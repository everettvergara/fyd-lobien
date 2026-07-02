<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Sign In') — Admin Portal</title>

    @include('partials.site-favicon')

    @vite(['resources/admin/scss/app.scss', 'resources/admin/js/app.js'])
</head>
<body class="admin-auth-body">
    <div class="admin-auth-wrapper d-flex align-items-center justify-content-center min-vh-100">
        <div class="admin-auth-card card shadow">
            <div class="card-body">
                <div class="text-center">
                    <h1 class="admin-auth-title mb-0">Admin Portal</h1>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show small" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show small" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <div class="admin-auth-version">
        <div class="admin-auth-version-label">v{{ $cmsVersion['version'] }}</div>
        <div class="admin-auth-version-meta">{{ $cmsVersion['template_name'] }}</div>
    </div>
</body>
</html>
