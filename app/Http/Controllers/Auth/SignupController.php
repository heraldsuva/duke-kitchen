<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SignupRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class SignupController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Signup');
    }

    public function store(SignupRequest $request): RedirectResponse
    {
        $user = User::query()->create([
            ...$request->safe()->only([
                'first_name',
                'last_name',
                'email_address',
                'user_name',
                'password',
            ]),
            'user_type' => UserType::Admin,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
