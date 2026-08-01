<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Notifications\AdminResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_page_loads(): void
    {
        $this->get(route('admin.password.request'))
            ->assertOk();
    }

    public function test_requesting_reset_link_sends_notification_for_existing_admin(): void
    {
        Notification::fake();

        $admin = Admin::factory()->create();

        $this->post(route('admin.password.email'), ['email' => $admin->email])
            ->assertSessionHas('success');

        Notification::assertSentTo($admin, AdminResetPassword::class);
    }

    public function test_requesting_reset_link_for_unknown_email_shows_generic_message_without_error(): void
    {
        Notification::fake();

        $response = $this->post(route('admin.password.email'), ['email' => 'nobody@example.com']);

        $response->assertSessionHas('success');
        $response->assertSessionDoesntHaveErrors();

        Notification::assertNothingSent();
    }

    public function test_admin_can_reset_password_with_valid_token(): void
    {
        $admin = Admin::factory()->create([
            'password' => bcrypt('old-password'),
        ]);

        $token = Password::broker('admins')->createToken($admin);

        $this->post(route('admin.password.update'), [
            'token' => $token,
            'email' => $admin->email,
            'password' => 'brand-new-password',
            'password_confirmation' => 'brand-new-password',
        ])->assertRedirect(route('admin.login'));

        $this->assertTrue(Hash::check('brand-new-password', $admin->fresh()->password));
    }

    public function test_reset_fails_with_invalid_token(): void
    {
        $admin = Admin::factory()->create([
            'password' => bcrypt('old-password'),
        ]);

        $this->post(route('admin.password.update'), [
            'token' => 'invalid-token',
            'email' => $admin->email,
            'password' => 'brand-new-password',
            'password_confirmation' => 'brand-new-password',
        ])->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('old-password', $admin->fresh()->password));
    }
}
