<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Sign In') — {{ config('fyd.name') }}</title>

    @vite(['resources/admin/scss/app.scss', 'resources/admin/js/app.js'])
</head>
<body class="admin-auth-body">
    <div class="admin-auth-wrapper d-flex align-items-center justify-content-center min-vh-100">
        <div class="admin-auth-card card shadow">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <h1 class="h4 fw-bold text-primary">{{ config('fyd.name') }}</h1>
                    <p class="text-muted small mb-0">Admin Portal</p>
                </div>

                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>
