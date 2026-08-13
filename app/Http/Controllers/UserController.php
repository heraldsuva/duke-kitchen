<?php

namespace App\Http\Controllers;

use App\Enums\UserType;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function __construct(private UserService $userService) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', User::class);

        $users = User::query()
            ->withCount(['sites', 'equipment'])
            ->orderBy('id', 'desc')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'full_name' => $user->full_name,
                'user_name' => $user->user_name,
                'email_address' => $user->email_address,
                'user_type' => $user->user_type->value,
                'sites_count' => $user->sites_count,
                'equipment_count' => $user->equipment_count,
            ]);

        return Inertia::render('Users/Index', [
            'users' => $users,
            'userTypes' => collect(UserType::cases())->map(fn (UserType $type) => [
                'label' => $type->label(),
                'value' => $type->value,
            ]),
            'authUserId' => $request->user()->id,
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->userService->create($request->validated());

        return redirect()
            ->route('users.index')
            ->with('success', 'User created successfully.');
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->userService->update($user, $request->validated());

        return redirect()
            ->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $this->userService->delete($request->user(), $user);

        return redirect()
            ->route('users.index')
            ->with('success', 'User deleted successfully.');
    }
}
