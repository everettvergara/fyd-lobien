<?php

namespace App\Modules\Themes\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Themes\Models\ThemeSettings;
use App\Modules\Themes\Requests\InstallThemeRequest;
use App\Modules\Themes\Requests\UpdateThemeSettingsRequest;
use App\Services\Theme\ThemeRegistryService;
use App\Services\Theme\ThemeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ThemeController extends Controller
{
    public function __construct(
        protected ThemeRegistryService $registry,
        protected ThemeService $themes,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', ThemeSettings::class);

        return view('themes::themes.index', [
            'installed' => $this->registry->installed(),
            'available' => $this->registry->available(),
            'activeTheme' => $this->themes->activeSlug(),
        ]);
    }

    public function update(UpdateThemeSettingsRequest $request): RedirectResponse
    {
        try {
            $summary = $this->themes->setActive($request->validated('active_theme'), auth()->id());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        $message = 'Public theme activated successfully. Run npm run build if you installed a new theme.';

        if (is_array($summary)) {
            $parts = [];

            if ($summary['preserved'] > 0) {
                $parts[] = "{$summary['preserved']} block(s) kept in matching regions";
            }

            if ($summary['remapped'] > 0) {
                $parts[] = "{$summary['remapped']} block(s) moved";
            }

            if ($parts !== []) {
                $message .= ' '.implode('; ', $parts).'.';
            }

            if ($summary['details'] !== []) {
                $message .= ' ('.implode(', ', array_unique($summary['details'])).')';
            }
        }

        return back()->with('success', $message);
    }

    public function install(InstallThemeRequest $request, string $slug): RedirectResponse
    {
        try {
            $overwrite = $request->boolean('overwrite');
            $result = $this->themes->installFromContrib($slug, auth()->id(), $overwrite);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        $message = "Theme [{$slug}] installed. Run npm run build before activating it on the public site.";

        if (! empty($result['warnings'])) {
            $message .= ' Warnings: '.implode(' ', $result['warnings']);
        }

        return back()->with('success', $message);
    }
}
