<div class="listing-brochure-info-layout">
    <div class="listing-brochure-info-images">
        @if (count($buildingImages) > 0)
            <div class="listing-brochure-image-frame">
                <img src="{{ $buildingImages[0]['full'] }}" alt="{{ $buildingImages[0]['alt'] }}">
            </div>
        @else
            <div class="listing-brochure-placeholder" style="min-height: 10rem;">
                <i class="{{ admin_icon('bi-building') }}" aria-hidden="true"></i>
                <span>No building photo</span>
            </div>
        @endif

        @if (count($mapImages) > 0)
            <div class="listing-brochure-image-frame">
                <img src="{{ $mapImages[0]['full'] }}" alt="{{ $mapImages[0]['alt'] }}">
            </div>
        @else
            <div class="listing-brochure-placeholder" style="min-height: 8rem;">
                <i class="{{ admin_icon('bi-geo-alt') }}" aria-hidden="true"></i>
                <span>No map image</span>
            </div>
        @endif
    </div>

    <div class="listing-brochure-info-details">
        <div class="listing-brochure-section-bar listing-brochure-section-bar--left">Property Information</div>
        <table class="listing-brochure-info-table">
            <tbody>
                @foreach ($propertyInfoRows as $row)
                    <tr>
                        <td>{{ $row['label'] }}</td>
                        <td>{{ $row['value'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="listing-brochure-notes">
    @include('propertylistings::brochures._disclaimer')
</div>
