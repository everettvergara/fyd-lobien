<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsSocialTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@fyd.local')->firstOrFail();
    }

    public function test_admin_can_save_social_links(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.settings.update'), [
            'settings' => [
                'social' => [
                    'facebook' => 'https://facebook.com/example',
                    'instagram' => 'https://instagram.com/example',
                    'linkedin' => 'https://linkedin.com/company/example',
                    'tiktok' => 'https://tiktok.com/@example',
                    'youtube' => 'https://youtube.com/@example',
                ],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertSame('https://facebook.com/example', Setting::where('group', 'social')->where('key', 'facebook')->value('value'));
        $this->assertSame('https://instagram.com/example', Setting::where('group', 'social')->where('key', 'instagram')->value('value'));
        $this->assertSame('https://linkedin.com/company/example', Setting::where('group', 'social')->where('key', 'linkedin')->value('value'));
        $this->assertSame('https://tiktok.com/@example', Setting::where('group', 'social')->where('key', 'tiktok')->value('value'));
        $this->assertSame('https://youtube.com/@example', Setting::where('group', 'social')->where('key', 'youtube')->value('value'));
    }

    public function test_social_links_allow_empty_values(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.settings.update'), [
            'settings' => [
                'social' => [
                    'facebook' => '',
                    'instagram' => '',
                    'linkedin' => '',
                    'tiktok' => '',
                    'youtube' => '',
                ],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_social_links_reject_invalid_urls(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.settings.update'), [
            'settings' => [
                'social' => [
                    'facebook' => 'not-a-valid-url',
                ],
            ],
        ]);

        $response->assertSessionHasErrors('settings.social.facebook');
    }

    public function test_public_page_includes_saved_social_links(): void
    {
        Setting::set('social', 'facebook', 'https://facebook.com/example');
        Setting::set('social', 'instagram', 'https://instagram.com/example');
        Setting::set('social', 'linkedin', 'https://linkedin.com/company/example');
        Setting::set('social', 'tiktok', 'https://tiktok.com/@example');
        Setting::set('social', 'youtube', 'https://youtube.com/@example');

        $response = $this->get('/');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('app.social')
            ->where('app.social.facebook', 'https://facebook.com/example')
            ->where('app.social.instagram', 'https://instagram.com/example')
            ->where('app.social.linkedin', 'https://linkedin.com/company/example')
            ->where('app.social.tiktok', 'https://tiktok.com/@example')
            ->where('app.social.youtube', 'https://youtube.com/@example')
        );
    }

    public function test_seeder_includes_social_defaults(): void
    {
        $this->assertDatabaseHas('settings', ['group' => 'social', 'key' => 'facebook']);
        $this->assertDatabaseHas('settings', ['group' => 'social', 'key' => 'instagram']);
        $this->assertDatabaseHas('settings', ['group' => 'social', 'key' => 'linkedin']);
        $this->assertDatabaseHas('settings', ['group' => 'social', 'key' => 'tiktok']);
        $this->assertDatabaseHas('settings', ['group' => 'social', 'key' => 'youtube']);
    }
}
