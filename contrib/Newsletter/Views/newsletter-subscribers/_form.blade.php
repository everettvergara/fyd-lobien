<div class="mb-3">
    <label for="newsletter_list_id" class="form-label">Newsletter list</label>
    <select class="form-select @error('newsletter_list_id') is-invalid @enderror" id="newsletter_list_id" name="newsletter_list_id" required>
        <option value="">Select list...</option>
        @foreach ($lists as $list)
            <option value="{{ $list->id }}" @selected(old('newsletter_list_id', $subscriber?->newsletter_list_id) == $list->id)>
                {{ $list->name }}
            </option>
        @endforeach
    </select>
    @error('newsletter_list_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label for="email" class="form-label">Email</label>
    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email"
           value="{{ old('email', $subscriber?->email) }}" required>
    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label for="name" class="form-label">Name</label>
    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
           value="{{ old('name', $subscriber?->name) }}">
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label for="status" class="form-label">Status</label>
    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
        <option value="active" @selected(old('status', $subscriber?->status ?? 'active') === 'active')>Active</option>
        <option value="unsubscribed" @selected(old('status', $subscriber?->status) === 'unsubscribed')>Unsubscribed</option>
    </select>
    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
