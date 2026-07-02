@extends('admin.layouts.app')

@section('title', 'Edit Profile')

@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Profile', 'url' => route('admin.profile.show')],
        ['label' => 'Edit'],
    ];

    $hasContactErrors = $errors->hasAny(['contact_number', 'province_id', 'city_id']);
    $hasAboutErrors = $errors->hasAny(['about_me']);
@endphp

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Edit Profile</h1>
        <a href="{{ route('admin.profile.show') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>

    <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row g-4 admin-form-columns">
            <div class="col-12 admin-form-main">
                <div class="accordion profile-edit-accordion" id="profileEditAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="profileAccountHeading">
                            <button
                                class="accordion-button py-2"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#profileAccountPanel"
                                aria-expanded="true"
                                aria-controls="profileAccountPanel"
                            >
                                Account Information
                            </button>
                        </h2>
                        <div
                            id="profileAccountPanel"
                            class="accordion-collapse collapse show"
                            aria-labelledby="profileAccountHeading"
                            data-bs-parent="#profileEditAccordion"
                        >
                            <div class="accordion-body py-3">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text"
                                           class="form-control @error('name') is-invalid @enderror"
                                           id="name"
                                           name="name"
                                           value="{{ old('name', $user->name) }}"
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-0">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email"
                                           class="form-control @error('email') is-invalid @enderror"
                                           id="email"
                                           name="email"
                                           value="{{ old('email', $user->email) }}"
                                           required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Changing your email will require re-verification.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="profileContactHeading">
                            <button
                                class="accordion-button py-2 {{ $hasContactErrors ? '' : 'collapsed' }}"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#profileContactPanel"
                                aria-expanded="{{ $hasContactErrors ? 'true' : 'false' }}"
                                aria-controls="profileContactPanel"
                            >
                                Contact &amp; Address
                            </button>
                        </h2>
                        <div
                            id="profileContactPanel"
                            class="accordion-collapse collapse {{ $hasContactErrors ? 'show' : '' }}"
                            aria-labelledby="profileContactHeading"
                            data-bs-parent="#profileEditAccordion"
                        >
                            <div class="accordion-body py-3">
                                <div class="mb-3">
                                    <label for="contact_number" class="form-label">Contact Number</label>
                                    <input type="tel"
                                           class="form-control @error('contact_number') is-invalid @enderror"
                                           id="contact_number"
                                           name="contact_number"
                                           value="{{ old('contact_number', $user->contact_number) }}"
                                           placeholder="e.g. +63 912 345 6789">
                                    @error('contact_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="province_id" class="form-label">Province</label>
                                    <select class="form-select @error('province_id') is-invalid @enderror" id="province_id" name="province_id">
                                        <option value="">Select province...</option>
                                        @foreach ($provinces as $province)
                                            <option value="{{ $province->id }}" @selected(old('province_id', $user->province_id) == $province->id)>
                                                {{ $province->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('province_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if ($provinces->isEmpty())
                                        <div class="form-text text-warning">No provinces available. Ask an administrator to run the address seeder.</div>
                                    @endif
                                </div>

                                <div class="mb-0">
                                    <label for="city_id" class="form-label">City / Municipality</label>
                                    <select class="form-select @error('city_id') is-invalid @enderror" id="city_id" name="city_id">
                                        <option value="">Select city...</option>
                                        @foreach ($cities as $city)
                                            <option value="{{ $city->id }}" @selected(old('city_id', $user->city_id) == $city->id)>
                                                {{ $city->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('city_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="profileAboutHeading">
                            <button
                                class="accordion-button py-2 {{ $hasAboutErrors ? '' : 'collapsed' }}"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#profileAboutPanel"
                                aria-expanded="{{ $hasAboutErrors ? 'true' : 'false' }}"
                                aria-controls="profileAboutPanel"
                            >
                                About Me
                            </button>
                        </h2>
                        <div
                            id="profileAboutPanel"
                            class="accordion-collapse collapse {{ $hasAboutErrors ? 'show' : '' }}"
                            aria-labelledby="profileAboutHeading"
                            data-bs-parent="#profileEditAccordion"
                        >
                            <div class="accordion-body py-3">
                                <label for="about_me" class="form-label visually-hidden">About Me</label>
                                <textarea class="form-control @error('about_me') is-invalid @enderror"
                                          id="about_me"
                                          name="about_me"
                                          rows="5"
                                          placeholder="Tell us a little about yourself...">{{ old('about_me', $user->about_me) }}</textarea>
                                @error('about_me')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mt-3">Save Changes</button>
            </div>

            <div class="col-12 admin-form-sidebar">
                <div class="accordion profile-edit-accordion" id="profilePhotoAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="profilePhotoHeading">
                            <button
                                class="accordion-button py-2"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#profilePhotoPanel"
                                aria-expanded="true"
                                aria-controls="profilePhotoPanel"
                            >
                                Profile Photo
                            </button>
                        </h2>
                        <div
                            id="profilePhotoPanel"
                            class="accordion-collapse collapse show"
                            aria-labelledby="profilePhotoHeading"
                        >
                            <div class="accordion-body py-3">
                                <div class="text-center mb-3">
                                    @if ($user->avatarUrl())
                                        <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}" class="rounded-circle" width="96" height="96" style="object-fit: cover;">
                                    @else
                                        <div class="rounded-circle bg-white d-inline-flex align-items-center justify-content-center" style="width:96px;height:96px;">
                                            <i class="{{ admin_icon('bi-person-circle') }} text-muted" style="font-size:4rem;"></i>
                                        </div>
                                    @endif
                                </div>

                                @include('media::partials.media-picker', [
                                    'name' => 'avatar_media_id',
                                    'label' => null,
                                    'value' => old('avatar_media_id', $user->avatar_media_id),
                                ])

                                <div class="mb-2">
                                    <label for="avatar" class="form-label">Or upload a photo</label>
                                    <input type="file" class="form-control @error('avatar') is-invalid @enderror" id="avatar" name="avatar" accept="image/*">
                                    @error('avatar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">JPG, PNG, or WebP up to 2 MB.</div>
                                </div>

                                <div class="form-check mb-0">
                                    <input type="hidden" name="remove_avatar" value="0">
                                    <input type="checkbox" class="form-check-input @error('remove_avatar') is-invalid @enderror" id="remove_avatar" name="remove_avatar" value="1" {{ old('remove_avatar') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="remove_avatar">Remove current photo</label>
                                    @error('remove_avatar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const provinceSelect = document.getElementById('province_id');
    const citySelect = document.getElementById('city_id');
    const selectedCityId = @json(old('city_id', $user->city_id));

    async function loadCities(provinceId, preserveCityId = null) {
        citySelect.innerHTML = '<option value="">Select city...</option>';

        if (!provinceId) {
            return;
        }

        citySelect.disabled = true;

        try {
            const response = await fetch(`{{ url('/admin/cities/by-province') }}/${provinceId}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();

            data.cities.forEach((city) => {
                const option = document.createElement('option');
                option.value = city.id;
                option.textContent = city.name;
                if (preserveCityId && Number(preserveCityId) === Number(city.id)) {
                    option.selected = true;
                }
                citySelect.appendChild(option);
            });
        } finally {
            citySelect.disabled = false;
        }
    }

    provinceSelect.addEventListener('change', () => {
        loadCities(provinceSelect.value);
    });

    if (provinceSelect.value && citySelect.options.length <= 1) {
        loadCities(provinceSelect.value, selectedCityId);
    }
});
</script>
@endpush
