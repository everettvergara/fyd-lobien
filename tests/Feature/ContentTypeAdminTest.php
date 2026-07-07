<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Content\Models\Content;
use App\Modules\Content\Models\ContentType;
use App\Support\ContentTypeRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentTypeAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        $this->seed();

        return User::where('email', 'admin@fyd.local')->first();
    }

    public function test_content_type_edit_form_shows_optional_slug_field(): void
    {
        $admin = $this->admin();

        $pageResponse = $this->actingAs($admin)->get('/admin/content-types/page/edit');
        $pageResponse->assertOk();
        $pageResponse->assertSee('name="slug"', false);
        $pageResponse->assertSee('id="slug"', false);
        $pageResponse->assertDontSee('value="page"', false);

        $articleResponse = $this->actingAs($admin)->get('/admin/content-types/article/edit');
        $articleResponse->assertOk();
        $articleResponse->assertSee('value="articles"', false);
    }

    public function test_admin_can_update_content_type_slug_and_clear_it(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put('/admin/content-types/article', [
            'slug' => 'company-news',
            'label' => 'Article',
            'description' => 'Blog post / news article',
            'icon' => 'bi-journal-text',
            'sort_order' => 1,
            'is_active' => 1,
        ])->assertRedirect(route('admin.content-types.index'));

        $this->assertDatabaseHas('content_types', [
            'key' => 'article',
            'slug' => 'company-news',
        ]);

        $this->actingAs($admin)->put('/admin/content-types/article', [
            'slug' => '',
            'label' => 'Article',
            'description' => 'Blog post / news article',
            'icon' => 'bi-journal-text',
            'sort_order' => 1,
            'is_active' => 1,
        ])->assertRedirect(route('admin.content-types.index'));

        $this->assertDatabaseHas('content_types', [
            'key' => 'article',
            'slug' => null,
        ]);
    }

    public function test_content_type_slug_must_be_unique(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/content-types', [
            'key' => 'news',
            'slug' => 'articles',
            'label' => 'News',
            'description' => 'Company news items',
            'icon' => 'bi-newspaper',
            'sort_order' => 10,
            'is_active' => 1,
        ])->assertSessionHasErrors('slug');
    }

    public function test_content_types_list_renders_master_entries(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->get('/admin/content-types');

        $response->assertOk();
        $response->assertSee('Content Types');
        $response->assertSee('Page');
        $response->assertSee('Article');
        $response->assertSee('bi-file-earmark-text', false);
    }

    public function test_admin_can_create_content_type_and_use_it_on_content_form(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/content-types', [
            'key' => 'news',
            'label' => 'News',
            'description' => 'Company news items',
            'icon' => 'bi-newspaper',
            'sort_order' => 10,
            'is_active' => 1,
        ])->assertRedirect(route('admin.content-types.index'));

        $this->assertDatabaseHas('content_types', ['key' => 'news', 'label' => 'News']);
        $this->assertTrue(app(ContentTypeRegistry::class)->has('news'));

        $response = $this->actingAs($admin)->get('/admin/content/create?content_type=news');
        $response->assertOk();
        $response->assertSee('value="news"', false);
        $response->assertSee('Company news items');
    }

    public function test_content_store_rejects_unknown_content_type(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->post('/admin/content', [
            'content_type' => 'invalid-type',
            'title' => 'Invalid Type Entry',
            'slug' => 'invalid-type-entry',
            'status' => 'draft',
        ]);

        $response->assertSessionHasErrors('content_type');
        $this->assertDatabaseMissing('contents', ['slug' => 'invalid-type-entry']);
    }

    public function test_cannot_delete_content_type_with_entries(): void
    {
        $admin = $this->admin();

        Content::create([
            'content_type' => 'page',
            'title' => 'Existing Page',
            'slug' => 'existing-page-type-test',
            'status' => 'draft',
            'author_id' => $admin->id,
        ]);

        $type = ContentType::where('key', 'page')->firstOrFail();

        $this->actingAs($admin)->delete('/admin/content-types/'.$type->key)
            ->assertRedirect();

        $this->assertDatabaseHas('content_types', ['key' => 'page']);
    }
}
