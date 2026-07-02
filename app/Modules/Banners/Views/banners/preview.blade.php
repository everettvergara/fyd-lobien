@extends('admin.layouts.app')

@section('title', 'Preview Banner')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Banners', 'url' => route('admin.banners.index')],
        ['label' => 'Preview'],
    ]" />

    <x-admin.page-header
        title="Preview Banner"
        :back-route="route('admin.banners.edit', $banner)"
    />

    <x-admin.card title="{{ $banner->name }}">
        @php
            $isInnerPage = ($payload['template']['key'] ?? null) === 'inner_page';
            $previewMinHeight = $isInnerPage ? '200px' : '320px';
            $titleClass = $isInnerPage ? 'h3' : 'display-5';
            $descriptionClass = $isInnerPage ? 'mb-0' : 'lead';
            $previewImageUrl = $payload['backgroundImage']['url'] ?? $payload['desktopImage']['url'] ?? null;
        @endphp
        <div class="border rounded overflow-hidden">
            <div
                class="p-5 text-center text-white d-flex align-items-center justify-content-center"
                style="min-height:{{ $previewMinHeight }};background:linear-gradient(rgba(0,0,0,.55),rgba(0,0,0,.55)){{ $previewImageUrl ? ', url('.$previewImageUrl.') center/cover' : '' }};"
            >
                <div class="mx-auto" style="max-width:760px;">
                    @if ($payload['subtitle'] ?? null)
                        <p class="text-uppercase small opacity-75">{{ $payload['subtitle'] }}</p>
                    @endif
                    <h1 class="{{ $titleClass }}">{{ $payload['title'] ?? $banner->name }}</h1>
                    @if ($payload['description'] ?? null)
                        <p class="{{ $descriptionClass }}">{{ $payload['description'] }}</p>
                    @endif
                    @if ($payload['buttonText'] ?? null)
                        <a href="{{ $payload['buttonUrl'] ?? '#' }}" class="btn btn-primary">{{ $payload['buttonText'] }}</a>
                    @endif
                </div>
            </div>
        </div>
    </x-admin.card>

    <x-admin.card title="Rendering Payload" class="mt-3">
        <pre class="small mb-0"><code>{{ json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
    </x-admin.card>
@endsection
