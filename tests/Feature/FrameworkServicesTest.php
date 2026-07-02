<?php

namespace Tests\Feature;

use App\Framework\MenuRegistry;
use App\Framework\ModuleRegistry;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Modules\Content\Models\Content;
use App\Services\MailConfigService;
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

        $this->assertNotNull(Gate::getPolicyFor(Content::class));
    }

    public function test_menu_registry_returns_items_for_authorized_user(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@fyd.local')->first();
        $sections = app(MenuRegistry::class)->sectionsFor($admin);

        $this->assertNotEmpty($sections);
        $labels = collect($sections)->flatMap(fn (array $section) => collect($section['items'])->pluck('label'));
        $this->assertTrue($labels->contains('Dashboard'));
        $this->assertTrue($labels->contains('Content Management'));
        $this->assertTrue($labels->contains('Content Types'));
        $this->assertFalse($labels->contains('Page'));
        $this->assertFalse($labels->contains('Article'));
        $this->assertTrue($labels->contains('Settings'));

        $rolesItem = collect($sections)
            ->flatMap(fn (array $section) => $section['items'])
            ->firstWhere('label', 'Roles');

        $this->assertNotNull($rolesItem);
        $this->assertSame('bi-shield-check', $rolesItem['icon']);
        $this->assertSame('bi bi-shield-fill-check', admin_icon($rolesItem['icon']));
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

        $this->assertTrue($names->contains('Content'));
        $this->assertTrue($names->contains('Dashboard'));
    }

    public function test_mail_config_service_applies_smtp_settings(): void
    {
        $this->seed();

        $settings = app(SettingsService::class);
        $settings->set('email', 'mail_driver', 'smtp');
        $settings->set('email', 'smtp_host', 'smtp.example.com');
        $settings->set('email', 'smtp_port', '465');
        $settings->set('email', 'smtp_encryption', 'ssl');
        $settings->set('email', 'smtp_username', 'user@example.com');
        $settings->set('email', 'smtp_password', 'secret');
        $settings->set('email', 'from_address', 'noreply@example.com');
        $settings->set('email', 'from_name', 'FYD Test');

        app(MailConfigService::class)->apply();

        $this->assertSame('smtp', config('mail.default'));
        $this->assertSame('smtp.example.com', config('mail.mailers.smtp.host'));
        $this->assertSame(465, config('mail.mailers.smtp.port'));
        $this->assertSame('ssl', config('mail.mailers.smtp.scheme'));
        $this->assertSame('user@example.com', config('mail.mailers.smtp.username'));
        $this->assertSame('secret', config('mail.mailers.smtp.password'));
        $this->assertSame('noreply@example.com', config('mail.from.address'));
        $this->assertSame('FYD Test', config('mail.from.name'));
    }

    public function test_mail_config_service_applies_sendmail_settings(): void
    {
        $this->seed();

        $settings = app(SettingsService::class);
        $settings->set('email', 'mail_driver', 'sendmail');
        $settings->set('email', 'sendmail_path', '/custom/sendmail -bs -i');
        $settings->set('email', 'from_address', 'mail@example.com');
        $settings->set('email', 'from_name', 'Sendmail Site');

        app(MailConfigService::class)->apply();

        $this->assertSame('sendmail', config('mail.default'));
        $this->assertSame('/custom/sendmail -bs -i', config('mail.mailers.sendmail.path'));
        $this->assertSame('mail@example.com', config('mail.from.address'));
        $this->assertSame('Sendmail Site', config('mail.from.name'));
    }

    public function test_mail_config_service_rejects_invalid_driver(): void
    {
        $this->seed();

        app(SettingsService::class)->set('email', 'mail_driver', 'invalid-driver');

        app(MailConfigService::class)->apply();

        $this->assertSame('array', config('mail.default'));
        $this->assertNotSame('invalid-driver', config('mail.default'));
    }
}
