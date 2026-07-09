@php
    $job = $job ?? null;
    $hasPublishingErrors = $errors->hasAny(['status', 'published_at', 'closing_date', 'sort_order']);
    $hasPictureErrors = $errors->has('picture_media_id');
@endphp

<div class="row g-4">
    <div class="col-lg-8">
        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title"
                   value="{{ old('title', $job?->title) }}" required>
            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="slug" class="form-label">Slug</label>
            <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug"
                   value="{{ old('slug', $job?->slug) }}" {{ $job ? 'required' : '' }}
                   placeholder="Auto-generated from title if left blank">
            <div class="form-text">Public URL: <code>/careers/your-slug</code></div>
            @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="department" class="form-label">Department</label>
                <input type="text" class="form-control @error('department') is-invalid @enderror" id="department" name="department"
                       value="{{ old('department', $job?->department) }}">
                @error('department')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="location" class="form-label">Location</label>
                <input type="text" class="form-control @error('location') is-invalid @enderror" id="location" name="location"
                       value="{{ old('location', $job?->location) }}">
                @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="employment_type" class="form-label">Employment Type</label>
                <select class="form-select @error('employment_type') is-invalid @enderror" id="employment_type" name="employment_type" required>
                    @foreach (\App\Modules\Careers\Models\CareerJob::employmentTypeLabels() as $value => $label)
                        <option value="{{ $value }}" @selected(old('employment_type', $job?->employment_type ?? 'full_time') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('employment_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="salary_range" class="form-label">Salary Range</label>
                <input type="text" class="form-control @error('salary_range') is-invalid @enderror" id="salary_range" name="salary_range"
                       value="{{ old('salary_range', $job?->salary_range) }}" placeholder="e.g. $50,000 - $70,000">
                @error('salary_range')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="mb-3">
            <label for="summary" class="form-label">Summary</label>
            <textarea class="form-control @error('summary') is-invalid @enderror" id="summary" name="summary" rows="2">{{ old('summary', $job?->summary) }}</textarea>
            @error('summary')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <x-admin.form.rich-text
                label="Description"
                name="description"
                :value="old('description', $job?->description)"
            />
            @error('description')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <x-admin.form.rich-text
                label="Requirements"
                name="requirements"
                :value="old('requirements', $job?->requirements)"
            />
            @error('requirements')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="col-lg-4">
        <div class="accordion" id="careerJobSidebar">
            <div class="accordion-item">
                <h2 class="accordion-header" id="careerPublishingHeading">
                    <button class="accordion-button py-2 {{ $hasPublishingErrors ? '' : 'collapsed' }}" type="button"
                            data-bs-toggle="collapse" data-bs-target="#careerPublishingPanel"
                            aria-expanded="{{ $hasPublishingErrors ? 'true' : 'false' }}">
                        Publishing
                    </button>
                </h2>
                <div id="careerPublishingPanel" class="accordion-collapse collapse {{ $hasPublishingErrors ? 'show' : '' }}">
                    <div class="accordion-body">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="draft" @selected(old('status', $job?->status ?? 'draft') === 'draft')>Draft</option>
                                <option value="published" @selected(old('status', $job?->status) === 'published')>Published</option>
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="published_at" class="form-label">Published At</label>
                            <input type="datetime-local" class="form-control @error('published_at') is-invalid @enderror"
                                   id="published_at" name="published_at"
                                   value="{{ old('published_at', $job?->published_at?->format('Y-m-d\TH:i')) }}">
                            @error('published_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="closing_date" class="form-label">Closing Date</label>
                            <input type="date" class="form-control @error('closing_date') is-invalid @enderror"
                                   id="closing_date" name="closing_date"
                                   value="{{ old('closing_date', $job?->closing_date?->format('Y-m-d')) }}">
                            @error('closing_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-0">
                            <label for="sort_order" class="form-label">Sort Order</label>
                            <input type="number" class="form-control @error('sort_order') is-invalid @enderror"
                                   id="sort_order" name="sort_order" min="0"
                                   value="{{ old('sort_order', $job?->sort_order ?? 0) }}">
                            @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="careerPictureHeading">
                    <button class="accordion-button py-2 {{ $hasPictureErrors ? '' : 'collapsed' }}" type="button"
                            data-bs-toggle="collapse" data-bs-target="#careerPicturePanel"
                            aria-expanded="{{ $hasPictureErrors ? 'true' : 'false' }}">
                        Picture
                    </button>
                </h2>
                <div id="careerPicturePanel" class="accordion-collapse collapse {{ $hasPictureErrors ? 'show' : '' }}">
                    <div class="accordion-body">
                        @include('media::partials.media-picker', [
                            'name' => 'picture_media_id',
                            'label' => 'Job Picture',
                            'mode' => 'single',
                            'value' => old('picture_media_id', $job?->picture_media_id),
                        ])
                        @error('picture_media_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
