<aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-sidebar-header p-3">
        <a href="{{ route('admin.dashboard') }}" class="admin-sidebar-brand text-decoration-none d-flex align-items-center gap-2">
            @if (!empty($app['logo']))
                <img src="{{ $app['logo'] }}" alt="" class="admin-sidebar-brand-logo">
            @endif
            <span class="admin-sidebar-brand-title text-truncate">{{ $app['name'] ?? config('fyd.name') }}</span>
        </a>
    </div>
    <nav class="admin-sidebar-nav">
        <ul class="nav flex-column">
            @foreach ($menuSections as $section)
                @if ($section['title'])
                    @php
                        $sectionKey = \Illuminate\Support\Str::slug($section['title']);
                        $sectionId = 'admin-nav-section-'.$sectionKey;
                        $sectionExpanded = collect($section['items'])->contains(fn (array $item) => $item['active']);
                    @endphp
                    <li class="nav-section">
                        <button
                            type="button"
                            class="nav-section-toggle"
                            data-bs-toggle="collapse"
                            data-bs-target="#{{ $sectionId }}"
                            data-admin-nav-section-key="{{ $sectionKey }}"
                            aria-expanded="{{ $sectionExpanded ? 'true' : 'false' }}"
                            aria-controls="{{ $sectionId }}"
                        >
                            <span>{{ $section['title'] }}</span>
                            <i class="bi bi-chevron-down nav-section-chevron" aria-hidden="true"></i>
                        </button>
                        <ul
                            id="{{ $sectionId }}"
                            class="nav-section-items nav flex-column collapse{{ $sectionExpanded ? ' show' : '' }}"
                        >
                            @foreach ($section['items'] as $item)
                                <li class="nav-item">
                                    <a href="{{ $item['url'] }}" class="nav-link {{ $item['active'] ? 'active' : '' }}">
                                        <i class="{{ admin_icon($item['icon']) }} me-2"></i> {{ $item['label'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @else
                    @foreach ($section['items'] as $item)
                        <li class="nav-item">
                            <a href="{{ $item['url'] }}" class="nav-link {{ $item['active'] ? 'active' : '' }}">
                                <i class="{{ admin_icon($item['icon']) }} me-2"></i> {{ $item['label'] }}
                            </a>
                        </li>
                    @endforeach
                @endif
            @endforeach
        </ul>
    </nav>

    @include('admin.layouts.partials.version-footer')
</aside>
