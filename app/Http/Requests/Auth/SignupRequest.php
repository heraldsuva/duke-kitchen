<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class SignupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email_address' => ['required', 'email', 'max:255', 'unique:users,email_address'],
            'user_name' => ['required', 'string', 'max:100', 'alpha_dash', 'unique:users,user_name'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }
}
