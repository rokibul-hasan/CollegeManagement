<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_log_in_and_out(): void
    {
        $admin = User::factory()->withRole('admin')->create(['password' => 'secret-pass']);

        $this->postJson('/api/admin/login', ['email' => $admin->email, 'password' => 'secret-pass'])
            ->assertOk()
            ->assertJsonPath('user.email', $admin->email);

        $this->assertAuthenticatedAs($admin);

        $this->postJson('/api/admin/logout')->assertOk();
        $this->assertGuest();
    }

    public function test_non_admin_users_cannot_log_in_to_the_portal(): void
    {
        $student = User::factory()->withRole('student')->create(['password' => 'secret-pass']);

        $this->postJson('/api/admin/login', ['email' => $student->email, 'password' => 'secret-pass'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->assertGuest();
    }

    public function test_admin_endpoints_reject_guests_and_non_admins(): void
    {
        $this->getJson('/api/admin/dashboard')->assertUnauthorized();

        $this->actingAs(User::factory()->withRole('teacher')->create())
            ->getJson('/api/admin/dashboard')
            ->assertForbidden();
    }

    public function test_admin_can_change_password_with_current_password(): void
    {
        $admin = User::factory()->withRole('admin')->create(['password' => 'old-password']);

        $this->actingAs($admin)->putJson('/api/admin/profile', [
            'name' => 'নতুন নাম',
            'email' => $admin->email,
            'current_password' => 'wrong',
            'password' => 'new-password-1',
            'password_confirmation' => 'new-password-1',
        ])->assertJsonValidationErrors('current_password');

        $this->actingAs($admin)->putJson('/api/admin/profile', [
            'name' => 'নতুন নাম',
            'email' => $admin->email,
            'current_password' => 'old-password',
            'password' => 'new-password-1',
            'password_confirmation' => 'new-password-1',
        ])->assertOk()->assertJsonPath('user.name', 'নতুন নাম');

        $this->assertTrue(password_verify('new-password-1', $admin->fresh()->password));
    }
}
