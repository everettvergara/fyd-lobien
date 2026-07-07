@extends('admin.layouts.app')
@section('title', 'Settings')
@php $breadcrumbs = [['label' => 'Dashboard', 'url' => route('admin.dashboard')], ['label' => 'Settings']]; @endphp
@section('content')
<div class="mb-4"><h1 class="h3 mb-0">Settings</h1></div>
<form method="POST" action="{{ route('admin.settings.update') }}">@csrf @method('PUT')
<ul class="nav nav-tabs mb-4" role="tablist">
<li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#general" type="button">General</button></li>
<li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#email" type="button">Email</button></li>
<li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#social" type="button">Social</button></li>
<li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#contact" type="button">Contact</button></li>
<li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#auth" type="button">Authentication</button></li>
</ul>
<div class="tab-content">
<div class="tab-pane fade show active" id="general">
<div class="card"><div class="card-body">
<div class="mb-3"><label class="form-label">Website Name</label><input type="text" class="form-control" name="settings[general][website_name]" value="{{ $settings['general']['website_name'] ?? config('fyd.name') }}"></div>
<div class="mb-3"><label class="form-label">Tagline</label><input type="text" class="form-control" name="settings[general][tagline]" value="{{ $settings['general']['tagline'] ?? '' }}"></div>
@include('media::partials.media-picker', [
    'name' => 'settings[general][site_logo_id]',
    'label' => 'Site Logo',
    'value' => $settings['general']['site_logo_id'] ?? null,
])
@include('media::partials.media-picker', [
    'name' => 'settings[general][favicon_id]',
    'label' => 'Favicon',
    'value' => $settings['general']['favicon_id'] ?? null,
])
<div class="mb-3 form-check">
<input type="hidden" name="settings[general][maintenance_mode]" value="0">
<input type="checkbox" class="form-check-input" id="maintenance_mode" name="settings[general][maintenance_mode]" value="1" @checked(filter_var($settings['general']['maintenance_mode'] ?? false, FILTER_VALIDATE_BOOLEAN))>
<label class="form-check-label" for="maintenance_mode">Enable maintenance mode</label>
<div class="form-text">When enabled, public visitors are redirected to the maintenance page. The admin panel remains accessible.</div>
</div>
<div class="mb-3">
<label class="form-label" for="maintenance_page_url">Maintenance page URL</label>
<input type="text" class="form-control" id="maintenance_page_url" name="settings[general][maintenance_page_url]" value="{{ $settings['general']['maintenance_page_url'] ?? '/site-maintenance' }}" placeholder="/site-maintenance">
<div class="form-text">Internal path where public visitors are sent during maintenance mode.</div>
</div>
</div></div></div>
<div class="tab-pane fade" id="email">
@php
    $mailDriver = old('settings.email.mail_driver', $settings['email']['mail_driver'] ?? 'log');
@endphp
<div class="card"><div class="card-body">
<div class="row">
<div class="col-md-6 mb-3">
<label class="form-label">Mail Driver</label>
<select class="form-select" id="mail_driver" name="settings[email][mail_driver]">
@foreach (['smtp' => 'SMTP', 'sendmail' => 'Sendmail', 'log' => 'Log (development)'] as $value => $label)
<option value="{{ $value }}" @selected($mailDriver === $value)>{{ $label }}</option>
@endforeach
</select>
</div>
</div>
<div id="email-smtp-fields" class="row email-driver-fields" data-driver="smtp" @if($mailDriver !== 'smtp') style="display:none" @endif>
<div class="col-md-6 mb-3"><label class="form-label">SMTP Host</label><input type="text" class="form-control" name="settings[email][smtp_host]" value="{{ $settings['email']['smtp_host'] ?? '' }}"></div>
<div class="col-md-6 mb-3"><label class="form-label">SMTP Port</label><input type="number" class="form-control" name="settings[email][smtp_port]" value="{{ $settings['email']['smtp_port'] ?? '587' }}" min="1" max="65535"></div>
<div class="col-md-6 mb-3">
<label class="form-label">Encryption</label>
<select class="form-select" name="settings[email][smtp_encryption]">
@foreach (['' => 'None', 'tls' => 'TLS', 'ssl' => 'SSL'] as $value => $label)
<option value="{{ $value }}" @selected(($settings['email']['smtp_encryption'] ?? '') === $value)>{{ $label }}</option>
@endforeach
</select>
</div>
<div class="col-md-6 mb-3"><label class="form-label">SMTP Username</label><input type="text" class="form-control" name="settings[email][smtp_username]" value="{{ $settings['email']['smtp_username'] ?? '' }}"></div>
<div class="col-md-6 mb-3"><label class="form-label">SMTP Password</label><input type="password" class="form-control" name="settings[email][smtp_password]" value="{{ $settings['email']['smtp_password'] ?? '' }}" autocomplete="new-password"></div>
</div>
<div id="email-sendmail-fields" class="row email-driver-fields" data-driver="sendmail" @if($mailDriver !== 'sendmail') style="display:none" @endif>
<div class="col-12 mb-3"><label class="form-label">Sendmail Path</label><input type="text" class="form-control" name="settings[email][sendmail_path]" value="{{ $settings['email']['sendmail_path'] ?? '/usr/sbin/sendmail -bs -i' }}"><div class="form-text">Command used to hand off messages to the local mail transfer agent.</div></div>
</div>
<div class="row">
<div class="col-md-6 mb-3"><label class="form-label">From Address</label><input type="email" class="form-control" name="settings[email][from_address]" value="{{ $settings['email']['from_address'] ?? '' }}"></div>
<div class="col-md-6 mb-3"><label class="form-label">From Name</label><input type="text" class="form-control" name="settings[email][from_name]" value="{{ $settings['email']['from_name'] ?? '' }}"></div>
</div></div></div></div>
<div class="tab-pane fade" id="social">
<div class="card"><div class="card-body">
@foreach (['facebook', 'instagram', 'linkedin', 'tiktok', 'youtube'] as $network)
<div class="mb-3"><label class="form-label text-capitalize">{{ $network }}</label><input type="url" class="form-control" name="settings[social][{{ $network }}]" value="{{ $settings['social'][$network] ?? '' }}"></div>
@endforeach
</div></div></div>
<div class="tab-pane fade" id="contact">
<div class="card"><div class="card-body">
<div class="mb-3"><label class="form-label">Email</label><input type="email" class="form-control" name="settings[contact][email]" value="{{ $settings['contact']['email'] ?? '' }}"></div>
<div class="mb-3"><label class="form-label">Phone</label><input type="text" class="form-control" name="settings[contact][phone]" value="{{ $settings['contact']['phone'] ?? '' }}"></div>
<div class="mb-3"><label class="form-label">Address</label><textarea class="form-control" name="settings[contact][address]" rows="2">{{ $settings['contact']['address'] ?? '' }}</textarea></div>
</div></div></div>
<div class="tab-pane fade" id="auth">
<div class="card"><div class="card-body">
<div class="mb-3 form-check">
<input type="hidden" name="settings[auth][registration_enabled]" value="0">
<input type="checkbox" class="form-check-input" id="registration_enabled" name="settings[auth][registration_enabled]" value="1" @checked(filter_var($settings['auth']['registration_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN))>
<label class="form-check-label" for="registration_enabled">Allow public registration</label>
</div>
<div class="row">
<div class="col-md-4 mb-3"><label class="form-label">Minimum Password Length</label><input type="number" class="form-control" name="settings[auth][password_min_length]" value="{{ $settings['auth']['password_min_length'] ?? 8 }}" min="6" max="128"></div>
<div class="col-md-4 mb-3"><label class="form-label">Login Max Attempts</label><input type="number" class="form-control" name="settings[auth][login_max_attempts]" value="{{ $settings['auth']['login_max_attempts'] ?? 5 }}" min="1" max="20"></div>
<div class="col-md-4 mb-3"><label class="form-label">Session Lifetime (minutes)</label><input type="number" class="form-control" name="settings[auth][session_lifetime]" value="{{ $settings['auth']['session_lifetime'] ?? 120 }}" min="5" max="1440"></div>
</div>
<div class="mb-2 form-check">
<input type="hidden" name="settings[auth][password_mixed_case]" value="0">
<input type="checkbox" class="form-check-input" id="password_mixed_case" name="settings[auth][password_mixed_case]" value="1" @checked(filter_var($settings['auth']['password_mixed_case'] ?? true, FILTER_VALIDATE_BOOLEAN))>
<label class="form-check-label" for="password_mixed_case">Require mixed case</label>
</div>
<div class="mb-2 form-check">
<input type="hidden" name="settings[auth][password_numbers]" value="0">
<input type="checkbox" class="form-check-input" id="password_numbers" name="settings[auth][password_numbers]" value="1" @checked(filter_var($settings['auth']['password_numbers'] ?? true, FILTER_VALIDATE_BOOLEAN))>
<label class="form-check-label" for="password_numbers">Require numbers</label>
</div>
<div class="mb-2 form-check">
<input type="hidden" name="settings[auth][password_symbols]" value="0">
<input type="checkbox" class="form-check-input" id="password_symbols" name="settings[auth][password_symbols]" value="1" @checked(filter_var($settings['auth']['password_symbols'] ?? false, FILTER_VALIDATE_BOOLEAN))>
<label class="form-check-label" for="password_symbols">Require symbols</label>
</div>
</div></div></div>
</div>
@can('update', App\Models\Setting::class)<button type="submit" class="btn btn-primary mt-3">Save Settings</button>@endcan
</form>
@push('scripts')
<script>
document.getElementById('mail_driver')?.addEventListener('change', function () {
    const driver = this.value;
    document.querySelectorAll('.email-driver-fields').forEach(function (el) {
        el.style.display = el.dataset.driver === driver ? '' : 'none';
    });
});
</script>
@endpush
@endsection
