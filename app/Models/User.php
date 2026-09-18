<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\AdminPermission;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * The owner account. Checked by email first so the System page keeps working
     * even when the permission tables are missing or not yet migrated.
     */
    public function isSuperAdmin(): bool
    {
        return strcasecmp($this->email, (string) config('college.super_admin_email')) === 0
            || $this->hasRole('super-admin');
    }

    /**
     * Whether the user may sign in to the admin portal.
     */
    public function canAccessAdmin(): bool
    {
        return $this->isSuperAdmin() || $this->hasPermissionTo(AdminPermission::AdminAccess->value);
    }
}
