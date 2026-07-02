@extends('admin.layouts.app')

@section('title', 'Cache Settings')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Cache'],
    ]" />

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Cache Settings</h1>
    </div>

    <form method="POST" action="{{ route('admin.cache.update') }}">
        @csrf
        @method('PUT')

        <div class="card mb-4">
            <div class="card-header">Public Page Cache</div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    Caches full responses for the homepage and published content pages only.
                    Search, admin, sitemap, and robots routes are never cached.
                </p>

                <div class="mb-3 form-check">
                    <input type="hidden" name="enabled" value="0">
                    <input type="checkbox" class="form-check-input" id="enabled" name="enabled" value="1"
                           {{ old('enabled', $settings['enabled']) ? 'checked' : '' }}>
                    <label class="form-check-label" for="enabled">Enable public page caching</label>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="ttl_days">Cache validity (days)</label>
                    <input type="number" class="form-control @error('ttl_days') is-invalid @enderror" id="ttl_days"
                           name="ttl_days" value="{{ old('ttl_days', $settings['ttl_days']) }}" min="1" max="365">
                    <div class="form-text">Default is 1 day. Cached pages are also cleared when content, banners, menus, or site settings change.</div>
                    @error('ttl_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        @can('update', App\Modules\Cache\Models\CacheSettings::class)
            <button type="submit" class="btn btn-primary">Save Settings</button>
        @endcan
    </form>

    @can('update', App\Modules\Cache\Models\CacheSettings::class)
        <form method="POST" action="{{ route('admin.cache.clear') }}" class="mt-4"
              onsubmit="return confirm('Clear all cached public pages?');">
            @csrf
            <button type="submit" class="btn btn-outline-danger">Clear Cache</button>
        </form>
    @endcan
@endsection
