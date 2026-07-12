@if (config('recaptcha.enabled'))
    data-recaptcha-form
    data-recaptcha-action="{{ $action }}"
    data-recaptcha-site-key="{{ config('recaptcha.site_key') }}"
@endif
