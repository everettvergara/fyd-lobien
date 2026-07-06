@php
    $brochureSiteName = ($siteInfo ?? app(\App\Services\NavigationService::class)->siteInfo())['name'] ?? config('fyd.name');
@endphp

<footer class="listing-brochure-footer">
    <p class="listing-brochure-footer__text">
        &copy; {{ now()->year }} {{ $brochureSiteName }}. All rights reserved.
    </p>
</footer>
