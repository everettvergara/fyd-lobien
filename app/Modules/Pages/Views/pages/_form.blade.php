@php $isEdit = isset($page); $seo = $page?->seoMeta; @endphp

<div class="mb-3">
    <label for="title" class="form-label">Title</label>
    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $page?->title) }}" required>
    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label for="slug" class="form-label">Slug</label>
    <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug', $page?->slug) }}" required>
    @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label for="summary" class="form-label">Summary</label>
    <textarea class="form-control" id="summary" name="summary" rows="2">{{ old('summary', $page?->summary) }}</textarea>
</div>
<div class="mb-3">
    <label for="content" class="form-label">Content</label>
    <textarea class="form-control" id="content" name="content" rows="8">{{ old('content', $page?->content) }}</textarea>
</div>
<div class="row mb-3">
    <div class="col-md-4">
        <label for="status" class="form-label">Status</label>
        <select class="form-select" id="status" name="status" required>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" {{ old('status', $page?->status?->value) === $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label for="template" class="form-label">Template</label>
        <input type="text" class="form-control" id="template" name="template" value="{{ old('template', $page?->template ?? 'default') }}">
    </div>
    <div class="col-md-4">
        <label for="parent_id" class="form-label">Parent Page</label>
        <select class="form-select" id="parent_id" name="parent_id">
            <option value="">None</option>
            @foreach ($pages as $parent)
                <option value="{{ $parent->id }}" {{ old('parent_id', $page?->parent_id) == $parent->id ? 'selected' : '' }}>{{ $parent->title }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="mb-3">
    @include('media::partials.media-picker', [
        'name' => 'featured_image_id',
        'label' => 'Featured Image',
        'value' => $page?->featured_image_id,
        'previewUrl' => $page?->featuredImage?->url(),
    ])
</div>

<hr class="my-4">
<h6 class="fw-semibold mb-3">Page Sections</h6>
<div id="sections-container">
    @php $sections = old('sections', $page?->sections?->toArray() ?? []); @endphp
    @forelse ($sections as $i => $section)
        <div class="card mb-2 section-row">
            <div class="card-body py-2">
                <div class="row align-items-center">
                    <div class="col-md-5">
                        <select class="form-select form-select-sm" name="sections[{{ $i }}][component_type]">
                            @foreach ($components as $comp)
                                <option value="{{ $comp }}" {{ ($section['component_type'] ?? '') === $comp ? 'selected' : '' }}>{{ str_replace('_', ' ', ucfirst($comp)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <input type="text" class="form-control form-control-sm" name="sections[{{ $i }}][settings][title]" placeholder="Section title" value="{{ $section['settings']['title'] ?? '' }}">
                    </div>
                    <div class="col-md-1"><button type="button" class="btn btn-sm btn-outline-danger remove-section">&times;</button></div>
                </div>
            </div>
        </div>
    @empty
    @endforelse
</div>
<button type="button" class="btn btn-sm btn-outline-secondary mb-4" id="add-section"><i class="bi bi-plus"></i> Add Section</button>

<hr class="my-4">
@include('seo::partials.seo-fields', ['seo' => $seo])

@push('scripts')
<script>
document.getElementById('add-section')?.addEventListener('click', function() {
    const container = document.getElementById('sections-container');
    const index = container.querySelectorAll('.section-row').length;
    const components = @json($components);
    let options = components.map(c => `<option value="${c}">${c.replace(/_/g, ' ')}</option>`).join('');
    container.insertAdjacentHTML('beforeend', `
        <div class="card mb-2 section-row"><div class="card-body py-2"><div class="row align-items-center">
            <div class="col-md-5"><select class="form-select form-select-sm" name="sections[${index}][component_type]">${options}</select></div>
            <div class="col-md-6"><input type="text" class="form-control form-control-sm" name="sections[${index}][settings][title]" placeholder="Section title"></div>
            <div class="col-md-1"><button type="button" class="btn btn-sm btn-outline-danger remove-section">&times;</button></div>
        </div></div></div>`);
});
document.getElementById('sections-container')?.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-section')) e.target.closest('.section-row').remove();
});
</script>
@endpush
