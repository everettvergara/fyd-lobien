<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') — {{ $app['name'] ?? config('fyd.name') }}</title>

    @include('partials.site-favicon')

    @vite(['resources/admin/scss/app.scss', 'resources/admin/js/app.js'])

    @stack('styles')
</head>
<body
    class="admin-body"
    data-media-picker-url="{{ route('admin.media.picker') }}"
    data-media-upload-url="{{ route('admin.media.store') }}"
    data-media-preference-url="{{ route('admin.media.preference') }}"
    @if (session('admin_sidebar_collapsed')) data-admin-sidebar-reset="1" @endif
>
    <script>
        try {
            if (window.localStorage.getItem('admin-sidebar-panel-hidden') === '1') {
                document.documentElement.classList.add('admin-sidebar-hidden');
            }
        } catch (e) {}
    </script>
    <div class="admin-wrapper d-flex">
        @include('admin.layouts.partials.sidebar')

        <div class="admin-content-wrapper flex-grow-1 d-flex flex-column">
            @include('admin.layouts.partials.navbar')

            <main class="admin-main flex-grow-1 p-3">
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
    @include('media::partials.media-picker-modal')
    @stack('scripts')
</body>
</html>
