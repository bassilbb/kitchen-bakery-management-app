<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'department' => null,
        ]);
    }

    private function staff(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_STAFF,
            'department' => User::DEPT_KITCHEN,
        ]);
    }

    public function test_guest_cannot_view_settings_page(): void
    {
        $this->get('/settings')->assertRedirect('/login');
    }

    public function test_staff_cannot_view_settings_page(): void
    {
        $this->actingAs($this->staff())->get('/settings')->assertForbidden();
    }

    public function test_admin_can_view_settings_page(): void
    {
        $this->actingAs($this->admin())->get('/settings')->assertOk();
    }

    public function test_admin_can_update_company_name(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post('/settings', ['company_name' => 'Golden Crust Bakery'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('Golden Crust Bakery', Setting::get(Setting::COMPANY_NAME_KEY));
    }

    public function test_admin_can_upload_logo(): void
    {
        Storage::fake('public');

        $admin = $this->admin();

        $this->actingAs($admin)
            ->post('/settings', [
                'logo' => UploadedFile::fake()->image('logo.png'),
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $path = Setting::get(Setting::LOGO_PATH_KEY);
        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);
        $this->assertNotNull(Setting::logoUrl());
    }

    public function test_logo_upload_replaces_previous_logo(): void
    {
        Storage::fake('public');

        $admin = $this->admin();

        $this->actingAs($admin)->post('/settings', [
            'logo' => UploadedFile::fake()->image('first.png'),
        ]);

        $first = Setting::get(Setting::LOGO_PATH_KEY);

        $this->actingAs($admin)->post('/settings', [
            'logo' => UploadedFile::fake()->image('second.png'),
        ]);

        $second = Setting::get(Setting::LOGO_PATH_KEY);

        $this->assertNotSame($first, $second);
        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($second);
    }

    public function test_invalid_logo_is_rejected(): void
    {
        Storage::fake('public');

        $admin = $this->admin();

        $this->actingAs($admin)
            ->from('/settings')
            ->post('/settings', [
                'logo' => UploadedFile::fake()->create('notes.txt', 10),
            ])
            ->assertSessionHasErrors('logo');

        $this->assertNull(Setting::get(Setting::LOGO_PATH_KEY));
    }
}
