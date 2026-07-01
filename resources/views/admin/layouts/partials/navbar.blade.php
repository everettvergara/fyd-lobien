<header class="admin-navbar navbar navbar-expand-lg bg-white border-bottom px-4">
    <button class="btn btn-link text-dark d-lg-none p-0 me-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-controls="adminSidebar">
        <i class="bi bi-list fs-4"></i>
    </button>

    <div class="ms-auto d-flex align-items-center gap-3">
        <div class="dropdown">
            <button class="btn btn-link text-dark text-decoration-none dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle fs-5 me-1"></i>
                <span class="d-none d-md-inline">{{ auth()->user()->name }}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('admin.profile.show') }}"><i class="bi bi-person me-2"></i>Profile</a></li>
                <li><a class="dropdown-item" href="{{ route('admin.password.change') }}"><i class="bi bi-key me-2"></i>Change Password</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
