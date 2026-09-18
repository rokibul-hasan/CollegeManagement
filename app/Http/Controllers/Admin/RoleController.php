<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AdminPermission;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Roles with their permissions, plus the permission catalogue.
     */
    public function index(Request $request): JsonResponse
    {
        $roles = Role::query()
            ->with('permissions:id,name')
            ->withCount('users')
            ->unless($request->user()->isSuperAdmin(), fn ($query) => $query->where('name', '!=', 'super-admin'))
            ->orderBy('id')
            ->get()
            ->map(fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
                'users_count' => $role->users_count,
                'permissions' => $role->permissions->pluck('name'),
                'is_system' => in_array($role->name, config('college.system_roles'), true),
                'is_locked' => $role->name === 'super-admin',
            ]);

        return response()->json([
            'roles' => $roles,
            'permissions' => collect(AdminPermission::cases())->map(fn (AdminPermission $permission) => [
                'name' => $permission->value,
                'label' => $permission->label(),
            ]),
            'grantable' => $this->grantablePermissions($request->user()),
        ]);
    }

    /**
     * Create a custom role.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9-]+$/', Rule::unique('roles', 'name')],
            ...$this->permissionRules($request->user()),
        ], $this->messages());

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($data['permissions'] ?? []);

        return response()->json(['id' => $role->id], 201);
    }

    /**
     * Change which permissions a role grants.
     */
    public function update(Request $request, Role $role): JsonResponse
    {
        abort_if($role->name === 'super-admin', 422, 'সুপার অ্যাডমিন রোল পরিবর্তন করা যাবে না।');

        $data = $request->validate($this->permissionRules($request->user()), $this->messages());

        if (! $request->user()->isSuperAdmin()) {
            // Keep permissions the actor cannot grant exactly as they were.
            $untouchable = $role->permissions->pluck('name')->diff($this->grantablePermissions($request->user()));
            $data['permissions'] = collect($data['permissions'] ?? [])->merge($untouchable)->unique()->values()->all();
        }

        $role->syncPermissions($data['permissions'] ?? []);

        return response()->json(['ok' => true]);
    }

    /**
     * Delete a custom role that nobody uses.
     */
    public function destroy(Role $role): JsonResponse
    {
        abort_if(in_array($role->name, config('college.system_roles'), true), 422, 'ডিফল্ট রোল মুছে ফেলা যাবে না।');
        abort_if($role->users()->exists(), 422, 'এই রোলে ইউজার আছে। আগে তাদের অন্য রোল দিন।');

        $role->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * @return array<int, string>
     */
    private function grantablePermissions(User $actor): array
    {
        if ($actor->isSuperAdmin()) {
            return AdminPermission::values();
        }

        return $actor->getAllPermissions()->pluck('name')->intersect(AdminPermission::values())->values()->all();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function permissionRules(User $actor): array
    {
        return [
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::in($this->grantablePermissions($actor))],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function messages(): array
    {
        return [
            'name.regex' => 'রোলের নামে শুধু ইংরেজি ছোট হাতের অক্ষর, সংখ্যা ও হাইফেন ব্যবহার করুন।',
            'permissions.*.in' => 'আপনার নিজের নেই এমন পারমিশন দেওয়া যাবে না।',
        ];
    }
}
