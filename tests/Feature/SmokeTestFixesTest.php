<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Enums\UserStatus;
use App\Models\User;
use App\Modules\Content\Models\Content;
use App\Services\SettingsService;
use App\Support\EmailVerificationUrl;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class SmokeTestFixesTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_hidden_and_blocked_when_settings_disabled(): void
    {
        $this->seed();

        app(SettingsService::class)->set('auth', 'registration_enabled', 'false', 'boolean');

        $this->get('/admin/login')->assertOk()->assertDontSee('Register');
        $this->get('/admin/register')->assertRedirect('/admin/login');
    }

    public function test_email_verification_link_marks_user_verified_without_login(): void
    {
        Event::fake([Verified::class]);

        $user = User::factory()->create([
            'status' => UserStatus::PendingVerification,
            'email_verified_at' => null,
        ]);

        $url = EmailVerificationUrl::forUser($user);

        $this->get(parse_url($url, PHP_URL_PATH).'?'.parse_url($url, PHP_URL_QUERY))
            ->assertRedirect('/admin/login')
            ->assertSessionHas('success');

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
        $this->assertEquals(UserStatus::Active, $user->status);
    }

    public function test_email_verification_accepts_html_encoded_query_string(): void
    {
        Event::fake([Verified::class]);

        $user = User::factory()->create([
            'status' => UserStatus::PendingVerification,
            'email_verified_at' => null,
        ]);

        $url = EmailVerificationUrl::forUser($user);
        $encodedQuery = str_replace('&', '&amp;', parse_url($url, PHP_URL_QUERY));
        $path = parse_url($url, PHP_URL_PATH);

        $this->get($path.'?'.$encodedQuery)
            ->assertRedirect('/admin/login')
            ->assertSessionHas('success');

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_content_create_form_renders_without_undefined_variable_error(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@fyd.local')->first();

        $this->actingAs($admin)->get('/admin/content/create')->assertOk()->assertSee('Create Content');
    }

    public function test_banner_create_form_renders_without_undefined_variable_error(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@fyd.local')->first();

        $this->actingAs($admin)->get('/admin/banners/create')->assertOk()->assertSee('Create Banner');
    }

    public function test_invalid_content_slug_is_rejected(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@fyd.local')->first();

        $response = $this->actingAs($admin)->post('/admin/content', [
            'content_type' => 'page',
            'title' => 'Bad Slug Content',
            'slug' => 'Invalid Slug!',
            'status' => ContentStatus::Draft->value,
        ]);

        $response->assertSessionHasErrors('slug');
    }

    public function test_author_can_only_update_own_content(): void
    {
        $this->seed();

        $author = User::where('email', 'author@fyd.local')->first();
        $admin = User::where('email', 'admin@fyd.local')->first();

        $ownContent = Content::create([
            'content_type' => 'article',
            'title' => 'Author Content',
            'slug' => 'author-content',
            'status' => ContentStatus::Draft,
            'author_id' => $author->id,
        ]);

        $otherContent = Content::create([
            'content_type' => 'article',
            'title' => 'Admin Content',
            'slug' => 'admin-content',
            'status' => ContentStatus::Draft,
            'author_id' => $admin->id,
        ]);

        $this->actingAs($author)->put("/admin/content/{$ownContent->id}", [
            'content_type' => 'article',
            'title' => 'Updated Author Content',
            'slug' => 'author-content',
            'status' => ContentStatus::Draft->value,
        ])->assertRedirect('/admin/content');

        $this->actingAs($author)->put("/admin/content/{$otherContent->id}", [
            'content_type' => 'article',
            'title' => 'Hacked',
            'slug' => 'admin-content',
            'status' => ContentStatus::Draft->value,
        ])->assertForbidden();
    }

    public function test_author_index_only_shows_own_content(): void
    {
        $this->seed();

        $author = User::where('email', 'author@fyd.local')->first();
        $admin = User::where('email', 'admin@fyd.local')->first();

        Content::create([
            'content_type' => 'article',
            'title' => 'Author Content',
            'slug' => 'author-content',
            'status' => ContentStatus::Draft,
            'author_id' => $author->id,
        ]);

        Content::create([
            'content_type' => 'article',
            'title' => 'Admin Content',
            'slug' => 'admin-content',
            'status' => ContentStatus::Draft,
            'author_id' => $admin->id,
        ]);

        $response = $this->actingAs($author)->get('/admin/content');

        $response->assertOk()->assertSee('Author Content')->assertDontSee('Admin Content');
    }

    public function test_content_index_shows_inline_action_icons(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@fyd.local')->first();

        $this->actingAs($admin)->get('/admin/content')->assertOk()->assertSee('aria-label="View"', false);
    }
}
