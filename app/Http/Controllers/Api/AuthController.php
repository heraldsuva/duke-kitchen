<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\SignupRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $login = $request->string('login')->toString();
        $password = $request->string('password')->toString();

        $user = User::query()
            ->with('role:id,name')
            ->where(function ($query) use ($login): void {
                $query->where('email_address', $login)
                    ->orWhere('user_name', $login);
            })
            ->first();

        if ($user === null || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => 'These credentials do not match our records.',
            ]);
        }

        $token = $user->createToken('auth')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $this->formatUser($user),
        ]);
    }

    public function signup(SignupRequest $request): JsonResponse
    {
        $adminRoleId = Role::query()
            ->firstOrCreate(['name' => UserType::Admin->value])
            ->id;

        $user = User::query()->create([
            ...$request->safe()->only([
                'first_name',
                'last_name',
                'email_address',
                'user_name',
                'password',
            ]),
            'role_id' => $adminRoleId,
        ]);

        $user->load('role:id,name');

        $token = $user->createToken('auth')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $this->formatUser($user),
        ], 201);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        if ($token !== null) {
            $token->delete();
        }

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    public function user(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load('role:id,name');

        return response()->json([
            'user' => $this->formatUser($user),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'full_name' => $user->full_name,
            'user_name' => $user->user_name,
            'email_address' => $user->email_address,
            'role_id' => $user->role_id,
            'role_name' => $user->role?->name,
            'is_super_admin' => $user->isSuperAdmin(),
        ];
    }
}
