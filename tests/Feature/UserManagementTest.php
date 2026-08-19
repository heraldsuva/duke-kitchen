<?php

use App\Enums\UserType;
use App\Models\Equipment;
use App\Models\Role;
use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('super admin can create a user', function () {
    $superAdmin = User::factory()->superAdmin()->create();
    $adminRole = Role::query()->firstOrCreate(['name' => UserType::Admin->value]);

    actingAsApiUser($superAdmin)
        ->postJson('/api/users', [
            'first_name' => 'Dana',
            'last_name' => 'Lim',
            'email_address' => 'dana@example.com',
            'user_name' => 'dana',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'role_id' => $adminRole->id,
        ])
        ->assertCreated();

    $this->assertDatabaseHas('users', [
        'user_name' => 'dana',
        'email_address' => 'dana@example.com',
        'role_id' => $adminRole->id,
    ]);
});

test('super admin cannot delete their own account', function () {
    $superAdmin = User::factory()->superAdmin()->create();

    actingAsApiUser($superAdmin)
        ->deleteJson("/api/users/{$superAdmin->id}")
        ->assertForbidden();
});

test('super admin cannot delete a user who owns sites or equipment', function () {
    $superAdmin = User::factory()->superAdmin()->create();
    $admin = User::factory()->admin()->create();
    Site::factory()->create(['user_id' => $admin->id]);
    Equipment::factory()->create(['user_id' => $admin->id]);

    actingAsApiUser($superAdmin)
        ->deleteJson("/api/users/{$admin->id}")
        ->assertUnprocessable()
        ->assertJsonValidationErrors('user');
});

test('duplicate username and email are rejected', function () {
    $superAdmin = User::factory()->superAdmin()->create();
    $adminRole = Role::query()->firstOrCreate(['name' => UserType::Admin->value]);

    User::factory()->create([
        'email_address' => 'taken@example.com',
        'user_name' => 'taken',
        'password' => Hash::make('Password1!'),
    ]);

    actingAsApiUser($superAdmin)
        ->postJson('/api/users', [
            'first_name' => 'Dup',
            'last_name' => 'User',
            'email_address' => 'taken@example.com',
            'user_name' => 'taken',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'role_id' => $adminRole->id,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email_address', 'user_name']);
});

test('super admin can delete a user with no owned records', function () {
    $superAdmin = User::factory()->superAdmin()->create();
    $admin = User::factory()->admin()->create();

    actingAsApiUser($superAdmin)
        ->deleteJson("/api/users/{$admin->id}")
        ->assertOk();

    $this->assertDatabaseMissing('users', ['id' => $admin->id]);
});
