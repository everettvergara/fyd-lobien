<aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-sidebar-header p-3">
        <a href="{{ route('admin.dashboard') }}" class="admin-sidebar-brand text-decoration-none">
            <span class="fw-bold text-white">{{ config('fyd.name') }}</span>
        </a>
    </div>

    <nav class="admin-sidebar-nav">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2 me-2"></i>
                    Dashboard
                </a>
            </li>

            <li class="nav-section-title">Content</li>

            <li class="nav-item">
                <a href="#" class="nav-link disabled">
                    <i class="bi bi-file-earmark-text me-2"></i>
                    Pages
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link disabled">
                    <i class="bi bi-journal-text me-2"></i>
                    Posts
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link disabled">
                    <i class="bi bi-image me-2"></i>
                    Banners
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link disabled">
                    <i class="bi bi-list-nested me-2"></i>
                    Menus
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link disabled">
                    <i class="bi bi-folder2-open me-2"></i>
                    Media
                </a>
            </li>

            <li class="nav-section-title">Administration</li>

            <li class="nav-item">
                <a href="#" class="nav-link disabled">
                    <i class="bi bi-people me-2"></i>
                    Users
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link disabled">
                    <i class="bi bi-shield-check me-2"></i>
                    Roles
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link disabled">
                    <i class="bi bi-gear me-2"></i>
                    Settings
                </a>
            </li>
        </ul>
    </nav>
</aside>
