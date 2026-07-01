<?php

namespace App\Modules\Settings\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Modules\Settings\Requests\UpdateSettingsRequest;
use App\Services\ActivityLogger;
use App\Services\AuthConfigService;
use App\Services\MailConfigService;
use App\Services\PasswordPolicyService;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(
        protected SettingsService $settings,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', Setting::class);

        $settings = [
            'general' => $this->settings->group('general'),
            'email' => $this->settings->group('email'),
            'social' => $this->settings->group('social'),
            'analytics' => $this->settings->group('analytics'),
            'contact' => $this->settings->group('contact'),
            'seo' => $this->settings->group('seo'),
            'auth' => $this->settings->group('auth'),
        ];

        return view('settings::settings.index', compact('settings'));
    }

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        foreach ($request->validated('settings') as $group => $items) {
            foreach ($items as $key => $value) {
                $type = $this->settingType($group, $key);
                if ($type === 'boolean') {
                    $stored = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
                } else {
                    $stored = (string) $value;
                }
                $this->settings->set($group, $key, $stored, $type);
            }
        }

        app(PasswordPolicyService::class)->apply();
        app(AuthConfigService::class)->apply();
        app(MailConfigService::class)->apply();
        ActivityLogger::log('settings', 'updated');

        return back()->with('success', 'Settings saved successfully.');
    }

    protected function settingType(string $group, string $key): string
    {
        $booleanKeys = [
            'auth' => ['registration_enabled', 'password_mixed_case', 'password_numbers', 'password_symbols'],
        ];

        if (in_array($key, $booleanKeys[$group] ?? [], true)) {
            return 'boolean';
        }

        return 'string';
    }
}
