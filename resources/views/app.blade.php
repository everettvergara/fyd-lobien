<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title inertia>{{ $app['name'] ?? config('fyd.name') }}</title>

    @include('partials.site-favicon')

    @vite(app(\App\Services\Theme\ThemeService::class)->viteAssets())
    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>
