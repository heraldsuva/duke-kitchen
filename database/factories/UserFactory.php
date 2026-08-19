<?php

namespace Database\Factories;

use App\Enums\UserType;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    private function roleId(UserType $type): int
    {
        return Role::query()
            ->firstOrCreate(['name' => $type->value])
            ->id;
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'role_id' => $this->roleId(UserType::Admin),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email_address' => fake()->unique()->safeEmail(),
            'user_name' => fake()->unique()->userName(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => $this->roleId(UserType::SuperAdmin),
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => $this->roleId(UserType::Admin),
        ]);
    }
}
