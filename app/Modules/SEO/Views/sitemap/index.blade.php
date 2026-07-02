@extends('admin.layouts.app')

@section('title', 'Sitemap Settings')

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Sitemap'],
    ]" />

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Sitemap Settings</h1>
        <a href="{{ route('sitemap') }}" class="btn btn-outline-secondary" target="_blank">View sitemap.xml</a>
    </div>

    <form method="POST" action="{{ route('admin.seo.sitemap.update') }}">
        @csrf
        @method('PUT')

        <div class="card mb-4">
            <div class="card-body">
                <div class="mb-3 form-check">
                    <input type="hidden" name="sitemap_enabled" value="0">
                    <input type="checkbox" class="form-check-input" id="sitemap_enabled" name="sitemap_enabled" value="1"
                           {{ old('sitemap_enabled', $settings['sitemap_enabled']) ? 'checked' : '' }}>
                    <label class="form-check-label" for="sitemap_enabled">Enable public sitemap</label>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Homepage</div>
            <div class="card-body">
                <div class="mb-3 form-check">
                    <input type="hidden" name="homepage_include" value="0">
                    <input type="checkbox" class="form-check-input" id="homepage_include" name="homepage_include" value="1"
                           {{ old('homepage_include', $settings['homepage_include']) ? 'checked' : '' }}>
                    <label class="form-check-label" for="homepage_include">Include homepage in sitemap</label>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="homepage_changefreq">Change Frequency</label>
                        <select class="form-select @error('homepage_changefreq') is-invalid @enderror" id="homepage_changefreq" name="homepage_changefreq">
                            @foreach (\App\Enums\SitemapChangeFrequency::options() as $value => $label)
                                <option value="{{ $value }}" @selected(old('homepage_changefreq', $settings['homepage_changefreq']) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('homepage_changefreq')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="homepage_priority">Priority</label>
                        <input type="number" class="form-control @error('homepage_priority') is-invalid @enderror" id="homepage_priority" name="homepage_priority"
                               value="{{ old('homepage_priority', $settings['homepage_priority']) }}" min="0" max="1" step="0.1">
                        @error('homepage_priority')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Content Defaults</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="default_changefreq_page">Pages — Change Frequency</label>
                        <select class="form-select @error('default_changefreq_page') is-invalid @enderror" id="default_changefreq_page" name="default_changefreq_page">
                            @foreach (\App\Enums\SitemapChangeFrequency::options() as $value => $label)
                                <option value="{{ $value }}" @selected(old('default_changefreq_page', $settings['default_changefreq_page']) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('default_changefreq_page')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="default_changefreq_article">Articles — Change Frequency</label>
                        <select class="form-select @error('default_changefreq_article') is-invalid @enderror" id="default_changefreq_article" name="default_changefreq_article">
                            @foreach (\App\Enums\SitemapChangeFrequency::options() as $value => $label)
                                <option value="{{ $value }}" @selected(old('default_changefreq_article', $settings['default_changefreq_article']) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('default_changefreq_article')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="default_priority">Default Priority</label>
                        <input type="number" class="form-control @error('default_priority') is-invalid @enderror" id="default_priority" name="default_priority"
                               value="{{ old('default_priority', $settings['default_priority']) }}" min="0" max="1" step="0.1">
                        @error('default_priority')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        @can('update', App\Modules\SEO\Models\SeoSettings::class)
            <button type="submit" class="btn btn-primary">Save Settings</button>
        @endcan
    </form>
@endsection
