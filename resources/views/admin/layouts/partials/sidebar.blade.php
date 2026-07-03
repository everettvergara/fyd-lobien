<aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-sidebar-header p-3">
        <a href="{{ route('admin.dashboard') }}" class="admin-sidebar-brand text-decoration-none d-flex align-items-center gap-2">
            @if (!empty($app['logo']))
                <img src="{{ $app['logo'] }}" alt="" class="admin-sidebar-brand-logo">
            @endif
            <span class="admin-sidebar-brand-title text-truncate">{{ $app['name'] ?? config('fyd.name') }}</span>
        </a>
    </div>
    <div class="admin-sidebar-panels">
        <nav class="admin-sidebar-nav admin-sidebar-nav-core" aria-label="Core navigation">
            @include('admin.layouts.partials.sidebar-nav-sections', ['sections' => $coreMenuSections ?? $menuSections ?? []])
        </nav>
        @if (!empty($businessMenuSections))
            <nav class="admin-sidebar-nav admin-sidebar-nav-business" aria-label="Business modules">
                @include('admin.layouts.partials.sidebar-nav-sections', ['sections' => $businessMenuSections])
            </nav>
        @endif
    </div>

    @include('admin.layouts.partials.version-footer')
</aside>
