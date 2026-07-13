@extends('admin.layouts.app')

@section('title', $brochureLabel ?? 'Brochure')

@section('content')
    <div class="listing-brochure-preview d-print-none">
        @include('propertylistings::brochures._print-toolbar', [
            'listing' => $listing,
            'brochureLabel' => $brochureLabel ?? 'Brochure',
        ])
    </div>

    <div class="listing-brochure-canvas">
        <div class="listing-brochure-document">
            @yield('brochure-content')
        </div>
    </div>
@endsection

@push('styles')
    @include('propertylistings::brochures._styles')
@endpush
