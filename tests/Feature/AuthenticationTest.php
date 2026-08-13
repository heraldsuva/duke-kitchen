<?php

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('user can login with email', function () {
    $user = User::factory()->create([
        'email_address' => 'anna@example.com',
        'user_name' => 'anna',
        'password' => Hash::make('Password1!'),
    ]);

    $response = $this->post('/login', [
        'login' => 'anna@example.com',
        'password' => 'Password1!',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user);
});

test('user can login with username', function () {
    User::factory()->create([
        'email_address' => 'ben@example.com',
        'user_name' => 'benadmin',
        'password' => Hash::make('Password1!'),
    ]);

    $this->post('/login', [
        'login' => 'benadmin',
        'password' => 'Password1!',
    ])->assertRedirect(route('dashboard'));

    $this->assertAuthenticated();
});

test('invalid credentials are rejected', function () {
    User::factory()->create([
        'email_address' => 'anna@example.com',
        'password' => Hash::make('Password1!'),
    ]);

    $this->from('/login')->post('/login', [
        'login' => 'anna@example.com',
        'password' => 'wrong-password',
    ])->assertRedirect('/login')
        ->assertSessionHasErrors('login');

    $this->assertGuest();
});

test('user can signup as admin only', function () {
    $response = $this->post('/signup', [
        'first_name' => 'Carla',
        'last_name' => 'Cruz',
        'email_address' => 'carla@example.com',
        'user_name' => 'carla',
        'password' => 'Password1!',
        'password_confirmation' => 'Password1!',
        'user_type' => UserType::SuperAdmin->value,
    ]);

    $response->assertRedirect(route('dashboard'));

    $user = User::query()->where('user_name', 'carla')->first();

    expect($user)->not->toBeNull()
        ->and($user->user_type)->toBe(UserType::Admin);

    $this->assertAuthenticatedAs($user);
});

test('user can logout', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/logout')
        ->assertRedirect(route('login'));

    $this->assertGuest();
});
