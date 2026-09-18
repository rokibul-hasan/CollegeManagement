<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        return User::query()->where('email', config('college.super_admin_email'))->sole();
    }

    public function test_default_super_admin_is_created_by_migrations_and_can_log_in(): void
    {
        $owner = $this->superAdmin();

        $this->assertTrue($owner->hasRole('super-admin'));

        $this->postJson('/api/admin/login', ['email' => $owner->email, 'password' => 'Bangladesh@#202699'])
            ->assertOk()
            ->assertJsonPath('user.is_super_admin', true);
    }

    public function test_only_the_super_admin_can_use_system_tools(): void
    {
        $admin = User::factory()->withRole('admin')->create();

        $this->actingAs($admin)->getJson('/api/admin/system')->assertForbidden();
        $this->actingAs($admin)->postJson('/api/admin/system/migrate')->assertForbidden();
        $this->actingAs($admin)->postJson('/api/admin/system/clear-cache')->assertForbidden();

        $owner = $this->superAdmin();

        $this->actingAs($owner)->getJson('/api/admin/system')
            ->assertOk()
            ->assertJsonPath('database.connected', true)
            ->assertJsonPath('pending_migrations', []);
        $this->actingAs($owner)->postJson('/api/admin/system/clear-cache')->assertOk()->assertJsonPath('ok', true);
        $this->actingAs($owner)->postJson('/api/admin/system/migrate')->assertOk()->assertJsonPath('ok', true);
    }

    public function test_editor_can_only_manage_notices(): void
    {
        $editor = User::factory()->withRole('editor')->create();

        $this->actingAs($editor)->getJson('/api/admin/notices')->assertOk();
        $this->actingAs($editor)->getJson('/api/admin/menus?location=main')->assertForbidden();
        $this->actingAs($editor)->postJson('/api/admin/settings', ['site_name' => 'x'])->assertForbidden();
        $this->actingAs($editor)->getJson('/api/admin/users')->assertForbidden();
        $this->actingAs($editor)->getJson('/api/admin/roles')->assertForbidden();
    }

    public function test_admins_cannot_see_or_touch_the_super_admin(): void
    {
        $admin = User::factory()->withRole('admin')->create();
        $owner = $this->superAdmin();

        $emails = collect($this->actingAs($admin)->getJson('/api/admin/users')->assertOk()->json('users.data'))->pluck('email');
        $this->assertNotContains($owner->email, $emails);

        $this->actingAs($admin)->putJson("/api/admin/users/{$owner->id}", [
            'name' => 'x', 'email' => 'hijack@example.com', 'role' => 'admin',
        ])->assertNotFound();
        $this->actingAs($admin)->deleteJson("/api/admin/users/{$owner->id}")->assertNotFound();

        $this->actingAs($admin)->postJson('/api/admin/users', [
            'name' => 'Second Owner', 'email' => 'second@example.com', 'role' => 'super-admin',
            'password' => 'password-123', 'password_confirmation' => 'password-123',
        ])->assertJsonValidationErrors('role');
    }

    public function test_super_admin_appears_in_the_user_list_only_for_the_super_admin(): void
    {
        $owner = $this->superAdmin();
        $admin = User::factory()->withRole('admin')->create();

        $ownView = collect($this->actingAs($owner)->getJson('/api/admin/users')->assertOk()->json('users.data'));
        $this->assertContains($owner->email, $ownView->pluck('email'));
        $this->assertTrue($ownView->firstWhere('email', $owner->email)['is_super_admin']);

        foreach ([$owner->email, $owner->name] as $search) {
            $this->actingAs($admin)->getJson('/api/admin/users?q='.urlencode($search))
                ->assertOk()
                ->assertJsonCount(0, 'users.data');
        }

        $this->actingAs($admin)->getJson('/api/admin/users?role=super-admin')->assertOk()->assertJsonCount(0, 'users.data');

        $roleNames = collect($this->actingAs($admin)->getJson('/api/admin/roles')->json('roles'))->pluck('name');
        $this->assertNotContains('super-admin', $roleNames);
    }

    public function test_user_managers_cannot_hand_out_roles_stronger_than_their_own(): void
    {
        Role::create(['name' => 'user-manager', 'guard_name' => 'web'])->syncPermissions(['admin.access', 'users.manage']);
        $manager = User::factory()->withRole('user-manager')->create();

        $this->actingAs($manager)->postJson('/api/admin/users', [
            'name' => 'Escalated', 'email' => 'escalated@example.com', 'role' => 'admin',
            'password' => 'password-123', 'password_confirmation' => 'password-123',
        ])->assertJsonValidationErrors('role');

        $this->actingAs($manager)->postJson('/api/admin/users', [
            'name' => 'Teacher', 'email' => 'teacher@example.com', 'role' => 'teacher',
            'password' => 'password-123', 'password_confirmation' => 'password-123',
        ])->assertCreated();
    }

    public function test_role_permissions_can_be_edited_but_super_admin_role_is_locked(): void
    {
        $owner = $this->superAdmin();
        $editor = Role::findByName('editor');

        $this->actingAs($owner)->putJson("/api/admin/roles/{$editor->id}", [
            'permissions' => ['admin.access', 'notices.manage', 'menus.manage'],
        ])->assertOk();
        $this->assertTrue($editor->fresh()->hasPermissionTo('menus.manage'));

        $superRole = Role::findByName('super-admin');
        $this->actingAs($owner)->putJson("/api/admin/roles/{$superRole->id}", ['permissions' => []])->assertUnprocessable();
        $this->actingAs($owner)->deleteJson("/api/admin/roles/{$editor->id}")->assertUnprocessable();
    }

    public function test_super_admin_email_cannot_be_changed(): void
    {
        $owner = $this->superAdmin();

        $this->actingAs($owner)->putJson('/api/admin/profile', ['name' => 'Owner', 'email' => 'other@example.com'])
            ->assertJsonValidationErrors('email');
    }
}
