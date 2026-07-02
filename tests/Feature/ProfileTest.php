<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Address\Models\City;
use App\Modules\Address\Models\Province;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        $this->seed();

        return User::where('email', 'admin@fyd.local')->first();
    }

    public function test_profile_edit_shows_new_form_fields(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->get('/admin/profile/edit');

        $response->assertOk();
        $response->assertSee('Profile Photo');
        $response->assertSee('Contact Number');
        $response->assertSee('About Me');
        $response->assertSee('province_id');
        $response->assertSee('city_id');
    }

    public function test_profile_show_displays_profile_fields(): void
    {
        $admin = $this->admin();
        $province = Province::where('name', 'Cebu')->firstOrFail();
        $city = City::where('province_id', $province->id)->where('name', 'Cebu City')->firstOrFail();

        $admin->update([
            'contact_number' => '+63 912 345 6789',
            'province_id' => $province->id,
            'city_id' => $city->id,
            'about_me' => "Line one\nLine two",
        ]);

        $response = $this->actingAs($admin->fresh(['province', 'city']))->get('/admin/profile');

        $response->assertOk();
        $response->assertSee($admin->name);
        $response->assertSee('+63 912 345 6789');
        $response->assertSee('Cebu');
        $response->assertSee('Cebu City');
        $response->assertSee('Line one');
        $response->assertSee('Line two');
        $response->assertSee('About Me');
    }

    public function test_profile_update_saves_fields_and_validates_city_province_pairing(): void
    {
        $admin = $this->admin();
        $province = Province::where('name', 'Cebu')->firstOrFail();
        $city = City::where('province_id', $province->id)->where('is_active', true)->firstOrFail();
        $otherProvince = Province::where('name', '!=', 'Cebu')->firstOrFail();

        $response = $this->actingAs($admin)->put('/admin/profile', [
            'name' => $admin->name,
            'email' => $admin->email,
            'contact_number' => '09123456789',
            'province_id' => $otherProvince->id,
            'city_id' => $city->id,
            'about_me' => 'Updated bio',
            'remove_avatar' => 0,
        ]);

        $response->assertSessionHasErrors('city_id');

        $this->actingAs($admin)->put('/admin/profile', [
            'name' => $admin->name,
            'email' => $admin->email,
            'contact_number' => '09123456789',
            'province_id' => $province->id,
            'city_id' => $city->id,
            'about_me' => 'Updated bio',
            'remove_avatar' => 0,
        ])->assertRedirect('/admin/profile');

        $admin->refresh();
        $this->assertSame('09123456789', $admin->contact_number);
        $this->assertSame($province->id, $admin->province_id);
        $this->assertSame($city->id, $admin->city_id);
        $this->assertSame('Updated bio', $admin->about_me);
    }

    public function test_profile_avatar_upload_works_without_media_create_permission(): void
    {
        Storage::fake('public');
        $this->seed();

        $user = User::factory()->create();
        $viewerRole = \App\Models\Role::where('name', 'viewer')->first();
        $user->syncRoles([$viewerRole->id]);

        $file = UploadedFile::fake()->create('avatar.jpg', 120, 'image/jpeg');

        $response = $this->actingAs($user)->put('/admin/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $file,
            'remove_avatar' => 0,
        ]);

        $response->assertRedirect('/admin/profile');
        $user->refresh();
        $this->assertNotNull($user->avatar_media_id);
        $this->assertNotNull($user->avatar);
    }
}
