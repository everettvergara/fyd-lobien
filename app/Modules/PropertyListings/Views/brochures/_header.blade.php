@props([
    'listing',
])

@php
    // Resolved here because this partial renders inside @component scopes
    // that do not inherit controller view data.
    $brochureSite = $siteInfo ?? app(\App\Services\NavigationService::class)->siteInfo();
    $siteLogo = $brochureSite['logo'] ?? null;
    $siteName = $brochureSite['name'] ?? config('fyd.name');
@endphp

<header class="listing-brochure-header">
    <div class="listing-brochure-header__hexagon-stack">
        <span class="listing-brochure-header__hexagon-backing" aria-hidden="true"></span>
        @if ($siteLogo)
            <img src="{{ url($siteLogo) }}" alt="{{ $siteName }}" class="listing-brochure-header__site-logo" data-brochure-site-logo>
        @else
            <span class="listing-brochure-header__logo-fallback" data-brochure-logo-fallback>{{ strtoupper(substr($siteName, 0, 1)) }}</span>
        @endif
        <img src="{{ \App\Modules\PropertyListings\Support\ListingBrochureTypes::hexagonFrameUrl() }}"
             alt=""
             class="listing-brochure-header__hexagon-frame"
             aria-hidden="true">
    </div>
    <div class="listing-brochure-header__banner">
        <h1 class="listing-brochure-header__title">{{ strtoupper($listing->name) }}</h1>
    </div>
</header>
