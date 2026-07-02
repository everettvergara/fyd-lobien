<?php

namespace App\Modules\Settings\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MediaUsage;
use App\Models\Setting;
use App\Modules\Cache\Services\PublicCacheService;
use App\Modules\Settings\Requests\UpdateSettingsRequest;
use App\Services\ActivityLogger;
use App\Services\AuthConfigService;
use App\Services\MailConfigService;
use App\Services\Media\MediaUsageService;
use App\Services\PasswordPolicyService;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(
        protected SettingsService $settings,
        protected MediaUsageService $usage,
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
        app(PublicCacheService::class)->clearAll();
        $this->syncGeneralMediaUsage();
        ActivityLogger::log('settings', 'updated');

        return back()->with('success', 'Settings saved successfully.');
    }

    protected function settingType(string $group, string $key): string
    {
        $booleanKeys = [
            'general' => ['maintenance_mode'],
            'auth' => ['registration_enabled', 'password_mixed_case', 'password_numbers', 'password_symbols'],
        ];

        if (in_array($key, $booleanKeys[$group] ?? [], true)) {
            return 'boolean';
        }

        return 'string';
    }

    protected function syncGeneralMediaUsage(): void
    {
        $fields = [
            'site_logo_id' => 'Site Logo',
            'favicon_id' => 'Favicon',
        ];

        foreach ($fields as $key => $label) {
            $setting = Setting::where('group', 'general')->where('key', $key)->first();

            if (! $setting) {
                continue;
            }

            if ($setting->value) {
                $this->usage->register((int) $setting->value, $setting, 'settings', $key, $label);
            } else {
                MediaUsage::where([
                    'usable_type' => Setting::class,
                    'usable_id' => $setting->id,
                    'field' => $key,
                ])->delete();
            }
        }
    }
}
