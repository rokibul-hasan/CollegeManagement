<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Start an admin session.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => 'ইমেইল অথবা পাসওয়ার্ড সঠিক নয়।']);
        }

        if (! $request->user()->canAccessAdmin()) {
            Auth::guard('web')->logout();

            throw ValidationException::withMessages(['email' => 'এই অ্যাকাউন্টের অ্যাডমিন প্যানেলে প্রবেশের অনুমতি নেই।']);
        }

        $request->session()->regenerate();

        return response()->json(['user' => $this->userPayload($request->user())]);
    }

    /**
     * End the current session.
     */
    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['ok' => true]);
    }

    /**
     * The signed-in admin with roles and permissions, or null.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json(['user' => $user?->canAccessAdmin() ? $this->userPayload($user) : null]);
    }

    /**
     * Update the signed-in admin's profile and optionally password.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => [
                'required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id),
                // The owner email identifies the super admin, so it cannot be changed from here.
                Rule::when($user->isSuperAdmin(), [Rule::in([$user->email])]),
            ],
            'current_password' => ['required_with:password', 'nullable', 'current_password'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ], ['email.in' => 'সুপার অ্যাডমিনের ইমেইল পরিবর্তন করা যাবে না।']);

        $user->fill(['name' => $data['name'], 'email' => $data['email']]);

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        return response()->json(['user' => $this->userPayload($user)]);
    }

    /**
     * @return array{id: int, name: string, email: string, is_super_admin: bool, roles: array<int, string>, permissions: array<int, string>}
     */
    private function userPayload(User $user): array
    {
        try {
            $roles = $user->getRoleNames()->all();
            $permissions = $user->getAllPermissions()->pluck('name')->all();
        } catch (QueryException) {
            // Permission tables not migrated yet — the super admin can still reach the System page.
            $roles = [];
            $permissions = [];
        }

        return $user->only(['id', 'name', 'email']) + [
            'is_super_admin' => $user->isSuperAdmin(),
            'roles' => $roles,
            'permissions' => $permissions,
        ];
    }
}
