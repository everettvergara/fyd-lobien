<aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-sidebar-header p-3">
        <a href="{{ route('admin.dashboard') }}" class="admin-sidebar-brand text-decoration-none">
            <span class="fw-bold text-white">{{ config('fyd.name') }}</span>
        </a>
    </div>
    <nav class="admin-sidebar-nav">
        <ul class="nav flex-column">
            @foreach ($menuSections as $section)
                @if ($section['title'])
                    <li class="nav-section-title">{{ $section['title'] }}</li>
                @endif
                @foreach ($section['items'] as $item)
                    <li class="nav-item">
                        <a href="{{ $item['url'] }}" class="nav-link {{ $item['active'] ? 'active' : '' }}">
                            <i class="bi {{ $item['icon'] }} me-2"></i> {{ $item['label'] }}
                        </a>
                    </li>
                @endforeach
            @endforeach
        </ul>
    </nav>
</aside>
