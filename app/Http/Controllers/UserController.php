<?php

namespace App\Http\Controllers;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
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
}
