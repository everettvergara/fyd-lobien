<?php

namespace App\Modules\Posts\Tests\Feature;

use App\Enums\ContentStatus;
use App\Models\User;
use App\Modules\Posts\Models\Post;
use App\Modules\Posts\Services\PostService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        $this->seed();

        return User::where('email', 'admin@fyd.local')->first();
    }

    public function test_admin_can_create_post_via_service(): void
    {
        $admin = $this->admin();

        $post = app(PostService::class)->create([
            'title' => 'Service Post',
            'slug' => 'service-post',
            'content' => 'Created via PostService',
            'status' => ContentStatus::Draft->value,
        ], $admin->id);

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'slug' => 'service-post']);
    }

    public function test_admin_can_create_post_through_http(): void
    {
        $response = $this->actingAs($this->admin())->post('/admin/posts', [
            'title' => 'HTTP Post',
            'slug' => 'http-post',
            'content' => 'Blog content',
            'status' => ContentStatus::Draft->value,
        ]);

        $response->assertRedirect('/admin/posts');
        $this->assertDatabaseHas('posts', ['slug' => 'http-post']);
    }

    public function test_post_index_shows_standardized_actions(): void
    {
        $admin = $this->admin();

        Post::create([
            'title' => 'Listed Post',
            'slug' => 'listed-post',
            'status' => ContentStatus::Draft,
            'author_id' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get('/admin/posts');

        $response->assertOk();
        $response->assertSee('Listed Post');
        $response->assertSee('Actions');
    }
}
