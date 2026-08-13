<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $login = $request->string('login')->toString();
        $password = $request->string('password')->toString();

        $user = User::query()
            ->where(function ($query) use ($login): void {
                $query->where('email_address', $login)
                    ->orWhere('user_name', $login);
            })
            ->first();

        if ($user === null || ! Hash::check($password, $user->password)) {
            return back()->withErrors([
                'login' => 'These credentials do not match our records.',
            ])->onlyInput('login');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }
}
