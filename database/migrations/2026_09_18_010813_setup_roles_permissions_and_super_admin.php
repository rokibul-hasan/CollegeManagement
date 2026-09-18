<?php

use App\Enums\AdminPermission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Bcrypt hash of the owner's initial password (the plain password is never stored in code).
     */
    private const SUPER_ADMIN_PASSWORD_HASH = '$2y$12$eqt449Rxfqgjqn2VBVWcieLhYCvb2EYYBC9UpVr7EvEXhQjZ2qk86';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (AdminPermission::values() as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $roles = [
            'super-admin' => [],
            'admin' => AdminPermission::values(),
            'editor' => [AdminPermission::AdminAccess->value, AdminPermission::NoticesManage->value],
            'teacher' => [],
            'student' => [],
        ];

        foreach ($roles as $name => $permissions) {
            Role::findOrCreate($name, 'web')->syncPermissions($permissions);
        }

        $this->moveLegacyRoleColumn();
        $this->createSuperAdmin();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role')->default('student')->after('email')->index();
            });
        }

        Role::query()->delete();
        Permission::query()->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Earlier builds stored a plain `role` string on users; convert it to Spatie roles.
     */
    private function moveLegacyRoleColumn(): void
    {
        if (! Schema::hasColumn('users', 'role')) {
            return;
        }

        $model = config('auth.providers.users.model');

        DB::table('users')->select(['id', 'role'])->orderBy('id')->each(function (object $row) use ($model) {
            $role = in_array($row->role, ['admin', 'teacher', 'student'], true) ? $row->role : 'student';
            $model::query()->find($row->id)?->assignRole($role);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropColumn('role');
        });
    }

    private function createSuperAdmin(): void
    {
        $email = config('college.super_admin_email');
        $model = config('auth.providers.users.model');

        if (! DB::table('users')->where('email', $email)->exists()) {
            DB::table('users')->insert([
                'name' => 'Super Admin',
                'email' => $email,
                'password' => self::SUPER_ADMIN_PASSWORD_HASH,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $model::query()->where('email', $email)->first()?->assignRole('super-admin');
    }
};
