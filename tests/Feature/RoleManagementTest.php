<?php

use App\Models\Role;
use App\Models\User;

test('any authenticated user can list roles', function () {
    $admin = User::factory()->admin()->create();

    actingAsApiUser($admin)
        ->getJson('/api/roles')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'users_count'],
            ],
        ]);
});

test('super admin can create a role', function () {
    $superAdmin = User::factory()->superAdmin()->create();

    actingAsApiUser($superAdmin)
        ->postJson('/api/roles', ['name' => 'Manager'])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Manager');

    $this->assertDatabaseHas('roles', ['name' => 'Manager']);
});

test('admin cannot create a role', function () {
    $admin = User::factory()->admin()->create();

    actingAsApiUser($admin)
        ->postJson('/api/roles', ['name' => 'Manager'])
        ->assertForbidden();
});

test('duplicate role name is rejected', function () {
    $superAdmin = User::factory()->superAdmin()->create();

    actingAsApiUser($superAdmin)
        ->postJson('/api/roles', ['name' => 'Admin'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});

test('super admin can update a role', function () {
    $superAdmin = User::factory()->superAdmin()->create();
    $role = Role::query()->create(['name' => 'Viewer']);

    actingAsApiUser($superAdmin)
        ->putJson("/api/roles/{$role->id}", ['name' => 'ReadOnly'])
        ->assertOk()
        ->assertJsonPath('data.name', 'ReadOnly');

    expect($role->fresh()->name)->toBe('ReadOnly');
});

test('admin cannot update a role', function () {
    $admin = User::factory()->admin()->create();
    $role = Role::query()->create(['name' => 'Viewer']);

    actingAsApiUser($admin)
        ->putJson("/api/roles/{$role->id}", ['name' => 'ReadOnly'])
        ->assertForbidden();
});

test('super admin can delete a role with no users', function () {
    $superAdmin = User::factory()->superAdmin()->create();
    $role = Role::query()->create(['name' => 'Temporary']);

    actingAsApiUser($superAdmin)
        ->deleteJson("/api/roles/{$role->id}")
        ->assertOk();

    $this->assertDatabaseMissing('roles', ['id' => $role->id]);
});

test('super admin cannot delete a role assigned to users', function () {
    $superAdmin = User::factory()->superAdmin()->create();
    $admin = User::factory()->admin()->create();

    actingAsApiUser($superAdmin)
        ->deleteJson("/api/roles/{$admin->role_id}")
        ->assertUnprocessable()
        ->assertJsonValidationErrors('role');
});

test('admin cannot delete a role', function () {
    $admin = User::factory()->admin()->create();
    $role = Role::query()->create(['name' => 'Temporary']);

    actingAsApiUser($admin)
        ->deleteJson("/api/roles/{$role->id}")
        ->assertForbidden();
});
