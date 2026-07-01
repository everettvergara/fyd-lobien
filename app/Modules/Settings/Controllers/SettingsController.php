<?php

namespace App\Modules\Settings\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Modules\Settings\Requests\UpdateSettingsRequest;
use App\Services\ActivityLogger;
use App\Services\MailConfigService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->hasPermission('settings.view'), 403);

        $settings = [
            'general' => $this->group('general'),
            'email' => $this->group('email'),
            'social' => $this->group('social'),
            'analytics' => $this->group('analytics'),
            'contact' => $this->group('contact'),
            'seo' => $this->group('seo'),
        ];

        return view('settings::settings.index', compact('settings'));
    }

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        foreach ($request->validated('settings') as $group => $items) {
            foreach ($items as $key => $value) {
                Setting::set($group, $key, $value);
            }
        }

        app(MailConfigService::class)->apply();
        ActivityLogger::log('settings', 'updated');

        return back()->with('success', 'Settings saved successfully.');
    }

    protected function group(string $group): array
    {
        return Setting::where('group', $group)->pluck('value', 'key')->toArray();
    }
}
