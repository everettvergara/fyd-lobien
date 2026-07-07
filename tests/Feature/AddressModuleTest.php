<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\User;
use App\Modules\Address\Models\City;
use App\Modules\Address\Models\Province;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AddressModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        $this->seed();

        return User::where('email', 'admin@fyd.local')->first();
    }

    protected function cebuProvince(): Province
    {
        return Province::where('name', 'Cebu')->firstOrFail();
    }

    public function test_admin_can_view_provinces_list(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->get('/admin/provinces?search=Cebu');

        $response->assertOk();
        $response->assertSee('Provinces');
        $response->assertSee('Cebu');
    }

    public function test_admin_can_create_province_and_city(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/provinces', [
            'name' => 'Test Province',
            'code' => 'TST',
            'is_active' => 1,
        ])->assertRedirect('/admin/provinces');

        $province = Province::where('name', 'Test Province')->first();
        $this->assertNotNull($province);

        $this->actingAs($admin)->post('/admin/cities', [
            'province_id' => $province->id,
            'name' => 'Test City',
            'is_active' => 1,
        ])->assertRedirect('/admin/cities');

        $this->assertDatabaseHas('cities', [
            'province_id' => $province->id,
            'name' => 'Test City',
        ]);
    }

    public function test_cities_by_province_returns_json(): void
    {
        $admin = $this->admin();
        $province = $this->cebuProvince();
        $city = City::where('province_id', $province->id)->where('is_active', true)->firstOrFail();
        City::create(['province_id' => $province->id, 'name' => 'Inactive Test City', 'is_active' => false]);

        $response = $this->actingAs($admin)->getJson("/admin/cities/by-province/{$province->id}");

        $response->assertOk();
        $response->assertJsonFragment(['name' => $city->name]);
        $response->assertJsonMissing(['name' => 'Inactive Test City']);
    }

    public function test_cannot_delete_province_with_cities(): void
    {
        $admin = $this->admin();
        $province = $this->cebuProvince();

        $response = $this->actingAs($admin)->delete("/admin/provinces/{$province->id}");

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('provinces', ['id' => $province->id]);
    }

    public function test_default_seed_includes_provinces_and_cities(): void
    {
        $this->seed();

        $this->assertGreaterThan(0, Province::count());
        $this->assertGreaterThan(0, City::count());
    }

    public function test_cannot_delete_city_assigned_to_user(): void
    {
        $admin = $this->admin();
        $province = $this->cebuProvince();
        $city = City::where('province_id', $province->id)->where('is_active', true)->firstOrFail();

        $admin->update(['province_id' => $province->id, 'city_id' => $city->id]);

        $response = $this->actingAs($admin)->delete("/admin/cities/{$city->id}");

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('cities', ['id' => $city->id]);
    }

    public function test_admin_can_create_province_and_city_with_summary_description_and_image(): void
    {
        $admin = $this->admin();
        Storage::disk('public')->put('address/cebu.png', 'image-content');

        $media = Media::create([
            'filename' => 'cebu.png',
            'original_filename' => 'cebu.png',
            'mime_type' => 'image/png',
            'extension' => 'png',
            'size' => 12,
            'disk' => 'public',
            'path' => 'address/cebu.png',
            'uploaded_by' => $admin->id,
        ]);

        $this->actingAs($admin)->post('/admin/provinces', [
            'name' => 'Content Province',
            'code' => 'CP',
            'summary' => 'Province summary text.',
            'description' => '<p>Province <strong>description</strong>.</p>',
            'image_id' => $media->id,
            'is_active' => 1,
        ])->assertRedirect('/admin/provinces');

        $province = Province::where('name', 'Content Province')->firstOrFail();

        $this->assertDatabaseHas('provinces', [
            'id' => $province->id,
            'summary' => 'Province summary text.',
            'description' => '<p>Province <strong>description</strong>.</p>',
            'image_id' => $media->id,
        ]);

        $this->actingAs($admin)
            ->get("/admin/provinces/{$province->id}")
            ->assertOk()
            ->assertSee('Province summary text.', false);

        $this->actingAs($admin)->post('/admin/cities', [
            'province_id' => $province->id,
            'name' => 'Content City',
            'summary' => 'City summary text.',
            'description' => '<p>City <em>description</em>.</p>',
            'image_id' => $media->id,
            'is_active' => 1,
        ])->assertRedirect('/admin/cities');

        $city = City::where('name', 'Content City')->firstOrFail();

        $this->assertDatabaseHas('cities', [
            'id' => $city->id,
            'summary' => 'City summary text.',
            'description' => '<p>City <em>description</em>.</p>',
            'image_id' => $media->id,
        ]);

        $this->actingAs($admin)
            ->get("/admin/cities/{$city->id}")
            ->assertOk()
            ->assertSee('City summary text.', false);
    }
}
