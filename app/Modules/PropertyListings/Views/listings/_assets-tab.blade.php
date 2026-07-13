<div class="listing-assets-tab">
    @include('propertylistings::listings._assets-upload-pane', [
        'listing' => $listing ?? null,
        'lookups' => $lookups,
    ])
    @include('propertylistings::listings._assets-list', [
        'listing' => $listing ?? null,
        'lookups' => $lookups,
    ])
    @include('propertylistings::listings._compare-image-preview-modal')
</div>
