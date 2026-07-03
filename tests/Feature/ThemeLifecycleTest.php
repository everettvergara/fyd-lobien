<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SettingsService;
use App\Services\Theme\ThemeRegistryService;
use App\Services\Theme\ThemeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ThemeLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_default_theme_is_discovered_as_installed(): void
    {
        $installed = app(ThemeRegistryService::class)->installed();

        $this->assertTrue($installed->contains(fn (array $theme) => $theme['slug'] === 'fyd-default'));
        $this->assertTrue((bool) $installed->firstWhere('slug', 'fyd-default')['valid']);
    }

    public function test_active_theme_defaults_to_fyd_default(): void
    {
        $this->assertSame('fyd-default', app(ThemeService::class)->activeSlug());
    }

    public function test_vite_assets_point_to_active_theme(): void
    {
        $assets = app(ThemeService::class)->viteAssets();

        $this->assertSame([
            'themes/fyd-default/scss/theme.scss',
            'themes/fyd-default/assets/app.js',
        ], $assets);
    }

    public function test_invalid_active_theme_falls_back_to_default(): void
    {
        app(SettingsService::class)->set('appearance', 'active_theme', 'missing-theme', 'string');

        $this->assertSame('fyd-default', app(ThemeService::class)->activeSlug());
    }

    public function test_lobien_theme_is_valid_with_page_shell(): void
    {
        $lobien = app(ThemeRegistryService::class)->installed()->firstWhere('slug', 'lobien');

        if ($lobien === null) {
            $this->markTestSkipped('lobien theme is not installed.');
        }

        $this->assertTrue((bool) $lobien['valid']);
        $this->assertNotEmpty($lobien['regions'] ?? []);
    }

    public function test_theme_with_warnings_can_be_activated(): void
    {
        $lobien = app(ThemeRegistryService::class)->findInstalled('lobien');

        if ($lobien === null || ! ($lobien['valid'] ?? false)) {
            $this->markTestSkipped('lobien theme is not installed or is invalid.');
        }

        app(ThemeService::class)->setActive('lobien');

        $this->assertSame('lobien', app(ThemeService::class)->activeSlug());
    }

    public function test_admin_can_view_public_themes_page(): void
    {
        $admin = User::where('email', 'admin@fyd.local')->first();

        $response = $this->actingAs($admin)->get(route('admin.themes.index'));

        $response->assertOk();
        $response->assertSee('Public Themes');
        $response->assertSee('fyd-default');
    }

    public function test_make_theme_scaffolds_contrib_theme_folder(): void
    {
        $slug = 'test-theme-'.str_replace('.', '', uniqid('', true));
        $target = base_path("contrib_themes/{$slug}");

        if (is_dir($target)) {
            File::deleteDirectory($target);
        }

        $this->artisan('make:theme', ['name' => $slug])
            ->assertSuccessful();

        $this->assertDirectoryExists($target);
        $this->assertFileExists("{$target}/theme.json");
        $this->assertFileExists("{$target}/js/Pages/Home.vue");

        $manifest = json_decode((string) file_get_contents("{$target}/theme.json"), true);
        $this->assertSame($slug, $manifest['slug']);

        File::deleteDirectory($target);
    }
}
