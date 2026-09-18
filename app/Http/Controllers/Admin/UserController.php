<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Users with their roles. Super admins are invisible to everyone else.
     */
    public function index(Request $request): JsonResponse
    {
        $users = User::query()
            ->with('roles:id,name')
            ->unless($request->user()->isSuperAdmin(), fn ($query) => $query
                ->where('email', '!=', config('college.super_admin_email'))
                ->whereDoesntHave('roles', fn ($roles) => $roles->where('name', 'super-admin')))
            ->when($request->string('q')->trim()->toString(), fn ($query, string $search) => $query
                ->where(fn ($inner) => $inner->where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%')))
            ->when($request->string('role')->toString(), fn ($query, string $role) => $query->role($role))
            ->orderBy('name')
            ->paginate(20);

        $users->getCollection()->transform(fn (User $user) => $this->format($user));

        return response()->json([
            'users' => $users,
            'roles' => $this->assignableRoles($request->user()),
        ]);
    }

    /**
     * Create a user with a role.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);
        $user->syncRoles([$data['role']]);

        return response()->json($this->format($user), 201);
    }

    /**
     * Update a user's details, role and optionally password.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $this->guardTarget($request->user(), $user);

        $data = $this->validated($request, $user);

        if ($user->is($request->user()) && ! $user->isSuperAdmin() && $data['role'] !== $user->getRoleNames()->first()) {
            abort(422, 'নিজের রোল নিজে পরিবর্তন করা যাবে না।');
        }

        $user->fill(['name' => $data['name'], 'email' => $data['email']]);

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        if (! $user->isSuperAdmin()) {
            $user->syncRoles([$data['role']]);
        }

        return response()->json($this->format($user->fresh()));
    }

    /**
     * Delete a user.
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        $this->guardTarget($request->user(), $user);

        abort_if($user->is($request->user()), 422, 'নিজের অ্যাকাউন্ট মুছে ফেলা যাবে না।');
        abort_if($user->isSuperAdmin(), 422, 'সুপার অ্যাডমিন অ্যাকাউন্ট মুছে ফেলা যাবে না।');

        $user->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * Other admins may not touch the super admin at all.
     */
    private function guardTarget(User $actor, User $target): void
    {
        abort_if($target->isSuperAdmin() && ! $actor->isSuperAdmin(), 404);

        $targetRole = $target->getRoleNames()->first();

        abort_if(
            ! $actor->isSuperAdmin() && ! $target->is($actor) && $targetRole
                && ! $this->assignableRoles($actor)->contains('name', $targetRole),
            403,
            'এই ইউজারকে পরিবর্তনের অনুমতি নেই।',
        );
    }

    /**
     * @return array{name: string, email: string, role: string, password?: string|null}
     */
    private function validated(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => [
                'required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user?->id),
                Rule::when((bool) $user?->isSuperAdmin(), [Rule::in([$user?->email])]),
            ],
            'role' => [$user?->isSuperAdmin() ? 'nullable' : 'required', Rule::in($this->assignableRoles($request->user())->pluck('name')->all())],
            'password' => [$user ? 'nullable' : 'required', 'confirmed', Password::min(8)],
        ], [
            'email.in' => 'সুপার অ্যাডমিনের ইমেইল পরিবর্তন করা যাবে না।',
            'role.in' => 'এই রোলটি দেওয়ার অনুমতি নেই।',
        ]);
    }

    /**
     * Roles the acting user may hand out: never super-admin, and for anyone but the
     * super admin only roles whose permissions they already hold (no escalation).
     *
     * @return Collection<int, array{id: int, name: string}>
     */
    private function assignableRoles(User $actor): Collection
    {
        $roles = Role::query()->where('name', '!=', 'super-admin')->with('permissions:id,name')->orderBy('id')->get();

        if (! $actor->isSuperAdmin()) {
            $held = $actor->getAllPermissions()->pluck('name');
            $roles = $roles->filter(fn (Role $role) => $role->permissions->pluck('name')->diff($held)->isEmpty())->values();
        }

        return $roles->map(fn (Role $role) => $role->only(['id', 'name']));
    }

    /**
     * @return array<string, mixed>
     */
    private function format(User $user): array
    {
        return $user->only(['id', 'name', 'email', 'created_at']) + [
            'role' => $user->getRoleNames()->first(),
            'is_super_admin' => $user->isSuperAdmin(),
        ];
    }
}
