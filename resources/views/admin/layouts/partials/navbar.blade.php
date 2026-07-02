<header class="admin-navbar navbar navbar-expand-lg bg-white px-3">
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-link text-dark d-lg-none p-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-controls="adminSidebar" aria-label="Open menu">
            <i class="{{ admin_icon('bi-list') }} fs-4"></i>
        </button>

        <button
            class="btn btn-link text-dark d-none d-lg-inline-flex align-items-center p-0 admin-sidebar-toggle-btn"
            type="button"
            data-admin-sidebar-toggle
            aria-label="Hide menu"
            aria-pressed="false"
            title="Hide menu"
        >
            <i class="{{ admin_icon('bi-layout-sidebar-inset') }} fs-5" data-sidebar-icon-visible aria-hidden="true"></i>
            <i class="{{ admin_icon('bi-layout-sidebar') }} fs-5 d-none" data-sidebar-icon-hidden aria-hidden="true"></i>
        </button>
    </div>

    <div class="ms-auto d-flex align-items-center gap-3">
        <div class="dropdown admin-account-menu">
            <button class="btn btn-link text-dark text-decoration-none dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                @if (auth()->user()->avatarUrl())
                    <img src="{{ auth()->user()->avatarUrl() }}" alt="{{ auth()->user()->name }}" class="rounded-circle" width="32" height="32" style="object-fit: cover;">
                @else
                    <i class="{{ admin_icon('bi-person-circle') }} fs-5"></i>
                @endif
                <span class="d-none d-md-inline">{{ auth()->user()->name }}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('admin.profile.show') }}"><i class="{{ admin_icon('bi-person') }} me-2"></i>Profile</a></li>
                <li><a class="dropdown-item" href="{{ route('admin.password.change') }}"><i class="{{ admin_icon('bi-key') }} me-2"></i>Change Password</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <i class="{{ admin_icon('bi-box-arrow-right') }} me-2"></i>Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
