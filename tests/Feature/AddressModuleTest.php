<?php

namespace Tests\Feature;

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
}
