@php
    $settings = old('settings', $newsletterList?->settings() ?? App\Modules\Newsletter\Models\NewsletterList::defaultSettings());
@endphp

<div class="mb-3">
    <label for="name" class="form-label">Name</label>
    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
           value="{{ old('name', $newsletterList?->name) }}" required>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label for="slug" class="form-label">Slug</label>
    <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug"
           value="{{ old('slug', $newsletterList?->slug) }}">
    <div class="form-text">Reference this list from Page Manager using the <strong>Newsletter</strong> block and this slug.</div>
    @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label for="description" class="form-label">Description</label>
    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $newsletterList?->description) }}</textarea>
    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3 form-check">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
           @checked(old('is_active', $newsletterList?->is_active ?? true))>
    <label class="form-check-label" for="is_active">Active</label>
</div>

<hr class="my-4">

<h2 class="h6 mb-3">Public form labels</h2>

<div class="mb-3">
    <label for="settings_subscribe_label" class="form-label">Subscribe button label</label>
    <input type="text" class="form-control @error('settings.subscribe_label') is-invalid @enderror"
           id="settings_subscribe_label" name="settings[subscribe_label]"
           value="{{ old('settings.subscribe_label', $settings['subscribe_label'] ?? '') }}">
    @error('settings.subscribe_label')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label for="settings_unsubscribe_label" class="form-label">Unsubscribe button label</label>
    <input type="text" class="form-control @error('settings.unsubscribe_label') is-invalid @enderror"
           id="settings_unsubscribe_label" name="settings[unsubscribe_label]"
           value="{{ old('settings.unsubscribe_label', $settings['unsubscribe_label'] ?? '') }}">
    @error('settings.unsubscribe_label')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label for="settings_success_subscribe" class="form-label">Subscribe success message</label>
    <input type="text" class="form-control @error('settings.success_subscribe') is-invalid @enderror"
           id="settings_success_subscribe" name="settings[success_subscribe]"
           value="{{ old('settings.success_subscribe', $settings['success_subscribe'] ?? '') }}">
    @error('settings.success_subscribe')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label for="settings_success_unsubscribe" class="form-label">Unsubscribe success message</label>
    <input type="text" class="form-control @error('settings.success_unsubscribe') is-invalid @enderror"
           id="settings_success_unsubscribe" name="settings[success_unsubscribe]"
           value="{{ old('settings.success_unsubscribe', $settings['success_unsubscribe'] ?? '') }}">
    @error('settings.success_unsubscribe')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label for="settings_placeholder_email" class="form-label">Email placeholder</label>
    <input type="text" class="form-control @error('settings.placeholder_email') is-invalid @enderror"
           id="settings_placeholder_email" name="settings[placeholder_email]"
           value="{{ old('settings.placeholder_email', $settings['placeholder_email'] ?? '') }}">
    @error('settings.placeholder_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<hr class="my-4">

<h2 class="h6 mb-3">Subscription form fields</h2>
<p class="text-muted small">Choose which fields appear on the public subscribe form and whether each is required.</p>

@php
    $formFields = [
        'name' => ['get' => 'get_name', 'require' => 'require_name', 'label' => 'Name'],
        'mobile_number' => ['get' => 'get_mobile_number', 'require' => 'require_mobile_number', 'label' => 'Mobile number'],
        'designation' => ['get' => 'get_designation', 'require' => 'require_designation', 'label' => 'Designation'],
        'company' => ['get' => 'get_company', 'require' => 'require_company', 'label' => 'Company'],
    ];
@endphp

<div class="table-responsive">
    <table class="table table-sm align-middle">
        <thead>
            <tr>
                <th scope="col">Field</th>
                <th scope="col" class="text-center">Collect</th>
                <th scope="col" class="text-center">Required</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($formFields as $key => $field)
                <tr>
                    <td>{{ $field['label'] }}</td>
                    <td class="text-center">
                        <input type="checkbox" class="form-check-input"
                               id="settings_{{ $field['get'] }}"
                               name="settings[{{ $field['get'] }}]"
                               value="1"
                               @checked(old("settings.{$field['get']}", $settings[$field['get']] ?? false))>
                    </td>
                    <td class="text-center">
                        <input type="checkbox" class="form-check-input"
                               id="settings_{{ $field['require'] }}"
                               name="settings[{{ $field['require'] }}]"
                               value="1"
                               @checked(old("settings.{$field['require']}", $settings[$field['require']] ?? false))>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
