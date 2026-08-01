<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Setting::get() caches forever; the array driver isn't reset by
        // RefreshDatabase, so clear it to avoid leaking state across tests.
        Cache::flush();
    }

    public function test_guest_cannot_access_settings_page(): void
    {
        $this->get(route('admin.settings.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_settings_page(): void
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.settings.index'))
            ->assertOk()
            ->assertSee('وضع الصيانة');
    }

    public function test_admin_can_enable_and_disable_maintenance_mode(): void
    {
        $admin = Admin::factory()->create();

        $this->assertSame('0', Setting::get('maintenance_mode', '0'));

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.settings.toggle-maintenance'))
            ->assertRedirect();

        $this->assertSame('1', Setting::get('maintenance_mode'));

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.settings.toggle-maintenance'))
            ->assertRedirect();

        $this->assertSame('0', Setting::get('maintenance_mode'));
    }

    public function test_public_pages_are_blocked_during_maintenance_mode(): void
    {
        Setting::set('maintenance_mode', '1');

        $this->get(route('home'))
            ->assertStatus(503);
    }

    public function test_admin_routes_stay_reachable_during_maintenance_mode(): void
    {
        Setting::set('maintenance_mode', '1');

        $this->get(route('admin.login'))
            ->assertOk();
    }

    public function test_public_pages_work_normally_when_maintenance_mode_is_off(): void
    {
        Setting::set('maintenance_mode', '0');

        $this->get(route('home'))
            ->assertOk();
    }

    public function test_admin_can_update_their_name_and_email(): void
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.settings.update-profile'), [
                'name' => 'New Name',
                'email' => 'new-email@example.com',
            ])
            ->assertRedirect();

        $this->assertSame('New Name', $admin->fresh()->name);
        $this->assertSame('new-email@example.com', $admin->fresh()->email);
    }

    public function test_profile_email_must_be_unique(): void
    {
        Admin::factory()->create(['email' => 'taken@example.com']);
        $admin = Admin::factory()->create(['email' => 'mine@example.com']);

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.settings.update-profile'), [
                'name' => 'New Name',
                'email' => 'taken@example.com',
            ])
            ->assertSessionHasErrors('email');

        $this->assertSame('mine@example.com', $admin->fresh()->email);
    }

    public function test_admin_can_change_password_with_correct_current_password(): void
    {
        $admin = Admin::factory()->create([
            'password' => bcrypt('old-password'),
        ]);

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.settings.update-password'), [
                'current_password' => 'old-password',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertRedirect();

        $this->assertTrue(
            Hash::check('new-password-123', $admin->fresh()->password)
        );
    }

    public function test_password_change_fails_with_wrong_current_password(): void
    {
        $admin = Admin::factory()->create([
            'password' => bcrypt('old-password'),
        ]);

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.settings.update-password'), [
                'current_password' => 'wrong-password',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(
            Hash::check('old-password', $admin->fresh()->password)
        );
    }
}
