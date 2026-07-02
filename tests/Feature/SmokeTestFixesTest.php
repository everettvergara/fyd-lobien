<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Enums\UserStatus;
use App\Models\User;
use App\Modules\Pages\Models\Page;
use App\Modules\Posts\Models\Post;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class SmokeTestFixesTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_hidden_and_blocked_when_env_disabled(): void
    {
        config(['fyd.registration_enabled' => false]);
        $this->seed();

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

        $url = URL::temporarySignedRoute(
            'admin.verification.verify',
            now()->addHour(),
            ['id' => $user->id, 'hash' => sha1($user->email)],
            absolute: false,
        );

        $this->get($url)
            ->assertRedirect('/admin/login')
            ->assertSessionHas('success');

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
        $this->assertEquals(UserStatus::Active, $user->status);
    }

    public function test_post_create_form_renders_without_undefined_variable_error(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@fyd.local')->first();

        $this->actingAs($admin)->get('/admin/posts/create')->assertOk()->assertSee('Create Post');
    }

    public function test_banner_create_form_renders_without_undefined_variable_error(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@fyd.local')->first();

        $this->actingAs($admin)->get('/admin/banners/create')->assertOk()->assertSee('Create Banner');
    }

    public function test_invalid_page_slug_is_rejected(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@fyd.local')->first();

        $response = $this->actingAs($admin)->post('/admin/pages', [
            'title' => 'Bad Slug Page',
            'slug' => 'Invalid Slug!',
            'status' => ContentStatus::Draft->value,
        ]);

        $response->assertSessionHasErrors('slug');
    }

    public function test_author_can_only_update_own_post(): void
    {
        $this->seed();

        $author = User::where('email', 'author@fyd.local')->first();
        $admin = User::where('email', 'admin@fyd.local')->first();

        $ownPost = Post::create([
            'title' => 'Author Post',
            'slug' => 'author-post',
            'status' => ContentStatus::Draft,
            'author_id' => $author->id,
        ]);

        $otherPost = Post::create([
            'title' => 'Admin Post',
            'slug' => 'admin-post',
            'status' => ContentStatus::Draft,
            'author_id' => $admin->id,
        ]);

        $this->actingAs($author)->put("/admin/posts/{$ownPost->id}", [
            'title' => 'Updated Author Post',
            'slug' => 'author-post',
            'status' => ContentStatus::Draft->value,
        ])->assertRedirect('/admin/posts');

        $this->actingAs($author)->put("/admin/posts/{$otherPost->id}", [
            'title' => 'Hacked',
            'slug' => 'admin-post',
            'status' => ContentStatus::Draft->value,
        ])->assertForbidden();
    }

    public function test_author_index_only_shows_own_posts(): void
    {
        $this->seed();

        $author = User::where('email', 'author@fyd.local')->first();
        $admin = User::where('email', 'admin@fyd.local')->first();

        Post::create([
            'title' => 'Author Post',
            'slug' => 'author-post',
            'status' => ContentStatus::Draft,
            'author_id' => $author->id,
        ]);

        Post::create([
            'title' => 'Admin Post',
            'slug' => 'admin-post',
            'status' => ContentStatus::Draft,
            'author_id' => $admin->id,
        ]);

        $response = $this->actingAs($author)->get('/admin/posts');

        $response->assertOk()->assertSee('Author Post')->assertDontSee('Admin Post');
    }

    public function test_pages_index_shows_actions_dropdown(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@fyd.local')->first();

        $this->actingAs($admin)->get('/admin/pages')->assertOk()->assertSee('Actions');
    }
}
