<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Newsletter\Models\NewsletterList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class NewsletterListSettingsScratchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $target = base_path('app/Modules/Newsletter');
        if (File::isDirectory($target)) {
            File::deleteDirectory($target);
        }
        File::copyDirectory(base_path('contrib/Newsletter'), $target);
    }

    public function test_admin_form_post_enables_all_collect_fields(): void
    {
        $this->seed();
        \Illuminate\Support\Facades\Artisan::call('module:install', ['name' => 'Newsletter', '--force' => true]);

        $list = NewsletterList::where('slug', 'site-updates')->firstOrFail();
        $admin = User::where('email', 'admin@fyd.local')->firstOrFail();

        $payload = [
            'name' => $list->name,
            'slug' => $list->slug,
            'description' => $list->description,
            'is_active' => '1',
            'settings' => [
                'subscribe_label' => 'Subscribe',
                'unsubscribe_label' => 'Unsubscribe',
                'success_subscribe' => 'Thanks',
                'success_unsubscribe' => 'Bye',
                'placeholder_email' => 'you@example.com',
                'get_name' => ['0', '1'],
                'require_name' => ['0', '1'],
                'get_mobile_number' => ['0', '1'],
                'require_mobile_number' => ['0', '1'],
                'get_designation' => ['0', '1'],
                'require_designation' => ['0', '1'],
                'get_company' => ['0', '1'],
                'require_company' => ['0', '1'],
            ],
        ];

        $this->actingAs($admin)
            ->put(route('admin.newsletter-lists.update', $list), $payload)
            ->assertRedirect(route('admin.newsletter-lists.index'));

        $list->refresh();

        $this->assertTrue($list->settings()['get_name']);
        $this->assertTrue($list->settings()['get_mobile_number']);
        $this->assertTrue($list->settings()['get_designation']);
        $this->assertTrue($list->settings()['get_company']);

        $fields = $list->fieldSettings();
        $this->assertTrue($fields['name']['enabled']);
        $this->assertTrue($fields['mobile_number']['enabled']);
        $this->assertTrue($fields['designation']['enabled']);
        $this->assertTrue($fields['company']['enabled']);
    }
}
