<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') — {{ config('fyd.name') }}</title>

    @vite(['resources/admin/scss/app.scss', 'resources/admin/js/app.js'])
</head>
<body class="admin-body">
    <div class="admin-wrapper d-flex">
        @include('admin.layouts.partials.sidebar')

        <div class="admin-content-wrapper flex-grow-1 d-flex flex-column">
            @include('admin.layouts.partials.navbar')

            <main class="admin-main flex-grow-1 p-4">
                @include('admin.layouts.partials.breadcrumb')

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @include('admin.layouts.partials.toast')
    @stack('scripts')
</body>
</html>
