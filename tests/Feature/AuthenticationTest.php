<?php

use App\Enums\UserType;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('user can login with email', function () {
    $user = User::factory()->create([
        'email_address' => 'anna@example.com',
        'user_name' => 'anna',
        'password' => Hash::make('Password1!'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'login' => 'anna@example.com',
        'password' => 'Password1!',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['token', 'token_type', 'user' => ['id', 'email_address']]);

    expect($response->json('user.id'))->toBe($user->id);
});

test('user can login with username', function () {
    User::factory()->create([
        'email_address' => 'ben@example.com',
        'user_name' => 'benadmin',
        'password' => Hash::make('Password1!'),
    ]);

    $this->postJson('/api/auth/login', [
        'login' => 'benadmin',
        'password' => 'Password1!',
    ])->assertOk()
        ->assertJsonPath('token_type', 'Bearer');
});

test('invalid credentials are rejected', function () {
    User::factory()->create([
        'email_address' => 'anna@example.com',
        'password' => Hash::make('Password1!'),
    ]);

    $this->postJson('/api/auth/login', [
        'login' => 'anna@example.com',
        'password' => 'wrong-password',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('login');
});

test('user can signup as admin only', function () {
    $response = $this->postJson('/api/auth/signup', [
        'first_name' => 'Carla',
        'last_name' => 'Cruz',
        'email_address' => 'carla@example.com',
        'user_name' => 'carla',
        'password' => 'Password1!',
        'password_confirmation' => 'Password1!',
    ]);

    $response->assertCreated()
        ->assertJsonStructure(['token', 'user']);

    $user = User::query()->where('user_name', 'carla')->first();
    $adminRole = Role::query()->where('name', UserType::Admin->value)->first();

    expect($user)->not->toBeNull()
        ->and($user->role_id)->toBe($adminRole->id);
});

test('user can logout', function () {
    $user = User::factory()->create();
    $token = apiTokenFor($user);

    $this->withToken($token)
        ->postJson('/api/auth/logout')
        ->assertOk();

    expect($user->fresh()->tokens)->toBeEmpty();
});

test('authenticated user can fetch profile', function () {
    $user = User::factory()->admin()->create();

    actingAsApiUser($user)
        ->getJson('/api/auth/user')
        ->assertOk()
        ->assertJsonPath('user.id', $user->id);
});
