<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class UserService
{
    /**
     * @param  array{first_name: string, last_name: string, email_address: string, user_name: string, password: string, role_id: int}  $data
     */
    public function create(array $data): User
    {
        return User::query()->create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email_address' => $data['email_address'],
            'user_name' => $data['user_name'],
            'password' => $data['password'],
            'role_id' => $data['role_id'],
        ]);
    }

    /**
     * @param  array{first_name: string, last_name: string, email_address: string, user_name: string, password?: string|null, role_id: int}  $data
     */
    public function update(User $user, array $data): User
    {
        $payload = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email_address' => $data['email_address'],
            'user_name' => $data['user_name'],
            'role_id' => $data['role_id'],
        ];

        if (! empty($data['password'])) {
            $payload['password'] = $data['password'];
        }

        $user->update($payload);

        return $user->refresh();
    }

    public function delete(User $actor, User $user): void
    {
        if ($actor->id === $user->id) {
            throw ValidationException::withMessages([
                'user' => 'You cannot delete your own currently authenticated account.',
            ]);
        }

        $sitesCount = $user->sites()->count();
        $equipmentCount = $user->equipment()->count();

        if ($sitesCount > 0 || $equipmentCount > 0) {
            throw ValidationException::withMessages([
                'user' => "Cannot delete this user because they currently own: {$sitesCount} site(s) and {$equipmentCount} equipment record(s).",
            ]);
        }

        $user->delete();
    }
}
