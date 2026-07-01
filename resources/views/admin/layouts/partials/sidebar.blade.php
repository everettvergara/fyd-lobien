<aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-sidebar-header p-3">
        <a href="{{ route('admin.dashboard') }}" class="admin-sidebar-brand text-decoration-none">
            <span class="fw-bold text-white">{{ config('fyd.name') }}</span>
        </a>
    </div>
    <nav class="admin-sidebar-nav">
        <ul class="nav flex-column">
            @if (auth()->user()->hasPermission('dashboard.view'))
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                </li>
            @endif
            <li class="nav-section-title">Content</li>
            @if (auth()->user()->hasPermission('pages.view'))
                <li class="nav-item"><a href="{{ route('admin.pages.index') }}" class="nav-link {{ request()->routeIs('admin.pages.*') ? 'active' : '' }}"><i class="bi bi-file-earmark-text me-2"></i> Pages</a></li>
            @endif
            @if (auth()->user()->hasPermission('posts.view'))
                <li class="nav-item"><a href="{{ route('admin.posts.index') }}" class="nav-link {{ request()->routeIs('admin.posts.*') ? 'active' : '' }}"><i class="bi bi-journal-text me-2"></i> Posts</a></li>
            @endif
            @if (auth()->user()->hasPermission('banners.view'))
                <li class="nav-item"><a href="{{ route('admin.banners.index') }}" class="nav-link {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}"><i class="bi bi-image me-2"></i> Banners</a></li>
            @endif
            @if (auth()->user()->hasPermission('menus.view'))
                <li class="nav-item"><a href="{{ route('admin.menus.index') }}" class="nav-link {{ request()->routeIs('admin.menus.*') ? 'active' : '' }}"><i class="bi bi-list-nested me-2"></i> Menus</a></li>
            @endif
            @if (auth()->user()->hasPermission('media.view'))
                <li class="nav-item"><a href="{{ route('admin.media.index') }}" class="nav-link {{ request()->routeIs('admin.media.*') ? 'active' : '' }}"><i class="bi bi-folder2-open me-2"></i> Media</a></li>
            @endif
            <li class="nav-section-title">Administration</li>
            @if (auth()->user()->hasPermission('users.view'))
                <li class="nav-item"><a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"><i class="bi bi-people me-2"></i> Users</a></li>
            @endif
            @if (auth()->user()->hasPermission('roles.view'))
                <li class="nav-item"><a href="{{ route('admin.roles.index') }}" class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}"><i class="bi bi-shield-check me-2"></i> Roles</a></li>
            @endif
            @if (auth()->user()->hasPermission('permissions.view'))
                <li class="nav-item"><a href="{{ route('admin.permissions.index') }}" class="nav-link {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}"><i class="bi bi-key me-2"></i> Permissions</a></li>
            @endif
            @if (auth()->user()->hasPermission('settings.view'))
                <li class="nav-item"><a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}"><i class="bi bi-gear me-2"></i> Settings</a></li>
            @endif
        </ul>
    </nav>
</aside>
