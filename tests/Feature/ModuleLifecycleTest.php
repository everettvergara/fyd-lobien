<?php

namespace Tests\Feature;

use App\Framework\MenuRegistry;
use App\Models\InstalledModule;
use App\Models\User;
use App\Services\Module\ModuleManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ModuleLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->copyDemoNotesModule();
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(base_path('app/Modules/DemoNotes'));

        parent::tearDown();
    }

    public function test_installable_module_is_discovered_when_copied_to_app_modules(): void
    {
        $discovered = collect(app(ModuleManagerService::class)->discoverInstallable())
            ->map(fn ($module) => $module->name());

        $this->assertTrue($discovered->contains('DemoNotes'));
    }

    public function test_install_creates_tables_and_permissions(): void
    {
        $this->seed();

        Artisan::call('module:install', ['name' => 'DemoNotes', '--force' => true]);

        $this->assertDatabaseHas('installed_modules', [
            'name' => 'DemoNotes',
            'status' => InstalledModule::STATUS_INSTALLED,
        ]);
        $this->assertTrue(\Schema::hasTable('demo_notes'));
        $this->assertDatabaseHas('permissions', ['name' => 'demo_notes.view']);
        $this->assertDatabaseCount('demo_notes', 3);
    }

    public function test_disable_hides_business_sidebar_group(): void
    {
        $this->seed();
        Artisan::call('module:install', ['name' => 'DemoNotes', '--force' => true]);

        $admin = User::where('email', 'admin@fyd.local')->first();
        $panels = app(MenuRegistry::class)->panelsFor($admin);

        $this->assertNotEmpty($panels['business']);

        Artisan::call('module:disable', ['name' => 'DemoNotes', '--force' => true]);

        $panels = app(MenuRegistry::class)->panelsFor($admin);
        $this->assertEmpty($panels['business']);
    }

    public function test_user_with_only_demo_tags_permission_sees_tags_not_notes(): void
    {
        $this->seed();
        Artisan::call('module:install', ['name' => 'DemoNotes', '--force' => true]);

        $user = User::factory()->create();
        $role = \App\Models\Role::where('name', 'viewer')->first();
        $user->roles()->sync([$role->id]);

        $permission = \App\Models\Permission::where('name', 'demo_tags.view')->first();
        $permission?->roles()->syncWithoutDetaching([$role->id]);

        $panels = app(MenuRegistry::class)->panelsFor($user->fresh());
        $labels = collect($panels['business'])->flatMap(fn ($section) => collect($section['items'])->pluck('label'));

        $this->assertTrue($labels->contains('Demo Tags'));
        $this->assertFalse($labels->contains('Demo Notes'));
    }

    public function test_uninstall_removes_tables_and_permissions(): void
    {
        $this->seed();
        Artisan::call('module:install', ['name' => 'DemoNotes', '--force' => true]);

        Artisan::call('module:uninstall', ['name' => 'DemoNotes', '--force' => true]);

        $this->assertDatabaseMissing('installed_modules', ['name' => 'DemoNotes']);
        $this->assertFalse(\Schema::hasTable('demo_notes'));
        $this->assertDatabaseMissing('permissions', ['name' => 'demo_notes.view']);
    }

    public function test_modules_admin_requires_confirmation_for_disable(): void
    {
        $this->seed();
        Artisan::call('module:install', ['name' => 'DemoNotes', '--force' => true]);

        $admin = User::where('email', 'admin@fyd.local')->first();

        $this->actingAs($admin)
            ->post(route('admin.modules.disable', 'DemoNotes'))
            ->assertSessionHasErrors('confirm');

        $this->assertDatabaseHas('installed_modules', [
            'name' => 'DemoNotes',
            'status' => InstalledModule::STATUS_INSTALLED,
        ]);
    }

    public function test_modules_admin_requires_module_name_for_uninstall(): void
    {
        $this->seed();
        Artisan::call('module:install', ['name' => 'DemoNotes', '--force' => true]);

        $admin = User::where('email', 'admin@fyd.local')->first();

        $this->actingAs($admin)
            ->post(route('admin.modules.uninstall', 'DemoNotes'), [
                'confirm' => '1',
                'module_name' => 'WrongName',
            ])
            ->assertSessionHasErrors('module_name');
    }

    protected function copyDemoNotesModule(): void
    {
        $source = base_path('contrib/DemoNotes');
        $target = base_path('app/Modules/DemoNotes');

        if (File::isDirectory($target)) {
            File::deleteDirectory($target);
        }

        File::copyDirectory($source, $target);
    }
}
