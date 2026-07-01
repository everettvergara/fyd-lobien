<?php

namespace Tests\Feature;

use App\Framework\MenuRegistry;
use App\Framework\ModuleRegistry;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Modules\Pages\Models\Page;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class FrameworkServicesTest extends TestCase
{
    use RefreshDatabase;

    public function test_module_registry_registers_policies(): void
    {
        $this->seed();

        $this->assertNotNull(Gate::getPolicyFor(Page::class));
    }

    public function test_menu_registry_returns_items_for_authorized_user(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $sections = app(MenuRegistry::class)->sectionsFor($admin);

        $this->assertNotEmpty($sections);
        $labels = collect($sections)->flatMap(fn (array $section) => collect($section['items'])->pluck('label'));
        $this->assertTrue($labels->contains('Dashboard'));
        $this->assertTrue($labels->contains('Pages'));
        $this->assertTrue($labels->contains('Settings'));
    }

    public function test_menu_registry_hides_items_without_permission(): void
    {
        $this->seed();

        $user = User::factory()->create();
        $sections = app(MenuRegistry::class)->sectionsFor($user);

        $this->assertEmpty($sections);
    }

    public function test_settings_service_caches_values(): void
    {
        Setting::create([
            'group' => 'general',
            'key' => 'website_name',
            'value' => 'Cached Site',
            'type' => 'string',
        ]);

        $service = app(SettingsService::class);

        $this->assertSame('Cached Site', $service->get('general', 'website_name'));
        $this->assertTrue(Cache::has('settings.general.website_name'));

        Setting::where('group', 'general')->where('key', 'website_name')->update(['value' => 'Changed']);

        $this->assertSame('Cached Site', $service->get('general', 'website_name'));

        $service->forget('general', 'website_name');

        $this->assertSame('Changed', $service->get('general', 'website_name'));
    }

    public function test_module_registry_contains_enabled_modules(): void
    {
        $registry = app(ModuleRegistry::class);

        $names = collect($registry->all())->map(fn ($module) => $module->name());

        $this->assertTrue($names->contains('Pages'));
        $this->assertTrue($names->contains('Dashboard'));
    }
}
