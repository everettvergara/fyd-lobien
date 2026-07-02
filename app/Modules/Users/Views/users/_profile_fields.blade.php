@if ($user)
    <h2 class="h6 text-muted text-uppercase mb-3">Profile</h2>

    @include('media::partials.media-picker', [
        'name' => 'avatar_media_id',
        'label' => 'Profile Photo',
        'value' => old('avatar_media_id', $user->avatar_media_id),
    ])

    <div class="mb-3">
        <label for="avatar" class="form-label">Or upload a photo</label>
        <input type="file" class="form-control @error('avatar') is-invalid @enderror" id="avatar" name="avatar" accept="image/*">
        @error('avatar')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-4 form-check">
        <input type="hidden" name="remove_avatar" value="0">
        <input type="checkbox" class="form-check-input @error('remove_avatar') is-invalid @enderror" id="remove_avatar" name="remove_avatar" value="1" {{ old('remove_avatar') ? 'checked' : '' }}>
        <label class="form-check-label" for="remove_avatar">Remove current photo</label>
        @error('remove_avatar')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
        <label for="contact_number" class="form-label">Contact Number</label>
        <input type="tel" class="form-control @error('contact_number') is-invalid @enderror" id="contact_number" name="contact_number"
               value="{{ old('contact_number', $user->contact_number) }}" placeholder="e.g. +63 912 345 6789">
        @error('contact_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
        <label for="province_id" class="form-label">Province</label>
        <select class="form-select @error('province_id') is-invalid @enderror" id="province_id" name="province_id">
            <option value="">Select province...</option>
            @foreach ($provinces ?? [] as $province)
                <option value="{{ $province->id }}" @selected(old('province_id', $user->province_id) == $province->id)>
                    {{ $province->name }}
                </option>
            @endforeach
        </select>
        @error('province_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
        <label for="city_id" class="form-label">City / Municipality</label>
        <select class="form-select @error('city_id') is-invalid @enderror" id="city_id" name="city_id">
            <option value="">Select city...</option>
            @foreach ($cities ?? [] as $city)
                <option value="{{ $city->id }}" @selected(old('city_id', $user->city_id) == $city->id)>
                    {{ $city->name }}
                </option>
            @endforeach
        </select>
        @error('city_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-4">
        <label for="about_me" class="form-label">About Me</label>
        <textarea class="form-control @error('about_me') is-invalid @enderror" id="about_me" name="about_me" rows="4"
                  placeholder="Tell us a little about this user...">{{ old('about_me', $user->about_me) }}</textarea>
        @error('about_me')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
@endif
