<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private UserService $userService) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $users = User::query()
            ->withCount(['sites', 'equipment'])
            ->with('role:id,name')
            ->orderBy('id', 'desc')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(fn (User $user) => $this->formatUser($user));

        return response()->json([
            'data' => $users,
        ]);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->create($request->validated());
        $user->load('role:id,name');

        return response()->json([
            'message' => 'User created successfully.',
            'data' => $this->formatUser($user->loadCount(['sites', 'equipment'])),
        ], 201);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $user = $this->userService->update($user, $request->validated());
        $user->load('role:id,name');

        return response()->json([
            'message' => 'User updated successfully.',
            'data' => $this->formatUser($user->loadCount(['sites', 'equipment'])),
        ]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        $this->userService->delete($request->user(), $user);

        return response()->json([
            'message' => 'User deleted successfully.',
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
            'sites_count' => $user->sites_count ?? 0,
            'equipment_count' => $user->equipment_count ?? 0,
        ];
    }
}
