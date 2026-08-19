<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        $roles = Role::query()
            ->withCount('users')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $roles->map(fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
                'users_count' => $role->users_count,
            ]),
        ]);
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = Role::query()->create($request->validated());

        return response()->json([
            'message' => 'Role created successfully.',
            'data' => ['id' => $role->id, 'name' => $role->name],
        ], 201);
    }

    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        $role->update($request->validated());

        return response()->json([
            'message' => 'Role updated successfully.',
            'data' => ['id' => $role->id, 'name' => $role->name],
        ]);
    }

    public function destroy(Request $request, Role $role): JsonResponse
    {
        $this->authorize('delete', $role);

        if ($role->users()->exists()) {
            throw ValidationException::withMessages([
                'role' => 'Cannot delete a role that is still assigned to users.',
            ]);
        }

        $role->delete();

        return response()->json([
            'message' => 'Role deleted successfully.',
        ]);
    }
}
