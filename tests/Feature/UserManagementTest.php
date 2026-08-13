<?php

use App\Enums\UserType;
use App\Models\Equipment;
use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('super admin can create a user', function () {
    $superAdmin = User::factory()->superAdmin()->create();

    $this->actingAs($superAdmin)
        ->post(route('users.store'), [
            'first_name' => 'Dana',
            'last_name' => 'Lim',
            'email_address' => 'dana@example.com',
            'user_name' => 'dana',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'user_type' => UserType::Admin->value,
        ])
        ->assertRedirect(route('users.index'));

    $this->assertDatabaseHas('users', [
        'user_name' => 'dana',
        'email_address' => 'dana@example.com',
        'user_type' => UserType::Admin->value,
    ]);
});

test('super admin cannot delete their own account', function () {
    $superAdmin = User::factory()->superAdmin()->create();

    $this->actingAs($superAdmin)
        ->delete(route('users.destroy', $superAdmin))
        ->assertForbidden();
});

test('super admin cannot delete a user who owns sites or equipment', function () {
    $superAdmin = User::factory()->superAdmin()->create();
    $admin = User::factory()->admin()->create();
    Site::factory()->create(['user_id' => $admin->id]);
    Equipment::factory()->create(['user_id' => $admin->id]);

    $this->actingAs($superAdmin)
        ->from(route('users.index'))
        ->delete(route('users.destroy', $admin))
        ->assertRedirect(route('users.index'))
        ->assertSessionHasErrors('user');
});

test('duplicate username and email are rejected', function () {
    $superAdmin = User::factory()->superAdmin()->create();

    User::factory()->create([
        'email_address' => 'taken@example.com',
        'user_name' => 'taken',
        'password' => Hash::make('Password1!'),
    ]);

    $this->actingAs($superAdmin)
        ->from(route('users.index'))
        ->post(route('users.store'), [
            'first_name' => 'Dup',
            'last_name' => 'User',
            'email_address' => 'taken@example.com',
            'user_name' => 'taken',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'user_type' => UserType::Admin->value,
        ])
        ->assertRedirect(route('users.index'))
        ->assertSessionHasErrors(['email_address', 'user_name']);
});

test('super admin can delete a user with no owned records', function () {
    $superAdmin = User::factory()->superAdmin()->create();
    $admin = User::factory()->admin()->create();

    $this->actingAs($superAdmin)
        ->delete(route('users.destroy', $admin))
        ->assertRedirect(route('users.index'));

    $this->assertDatabaseMissing('users', ['id' => $admin->id]);
});
