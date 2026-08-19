<?php

use App\Models\Equipment;
use App\Models\Site;
use App\Models\User;

test('admin cannot access user maintenance', function () {
    $admin = User::factory()->admin()->create();

    actingAsApiUser($admin)
        ->get(route('users.index'))
        ->assertForbidden();
});

test('super admin can access user maintenance', function () {
    $superAdmin = User::factory()->superAdmin()->create();

    actingAsApiUser($superAdmin)
        ->get(route('users.index'))
        ->assertOk();
});

test('admin cannot access another users site', function () {
    $admin = User::factory()->admin()->create();
    $other = User::factory()->admin()->create();
    $site = Site::factory()->create(['user_id' => $other->id]);

    actingAsApiUser($admin)
        ->get(route('sites.equipment', $site))
        ->assertForbidden();
});

test('admin cannot update another users site', function () {
    $admin = User::factory()->admin()->create();
    $other = User::factory()->admin()->create();
    $site = Site::factory()->create(['user_id' => $other->id]);

    actingAsApiUser($admin)
        ->putJson("/api/sites/{$site->id}", [
            'description' => 'Hacked Site',
            'active' => true,
        ])
        ->assertForbidden();
});

test('admin cannot access another users equipment', function () {
    $admin = User::factory()->admin()->create();
    $other = User::factory()->admin()->create();
    $equipment = Equipment::factory()->create(['user_id' => $other->id]);

    actingAsApiUser($admin)
        ->putJson("/api/equipment/{$equipment->id}", [
            'serial_number' => $equipment->serial_number,
            'description' => 'Hacked',
            'condition' => 'Working',
            'site_id' => null,
        ])
        ->assertForbidden();
});

test('admin cannot create a site for another user by manipulating user_id', function () {
    $admin = User::factory()->admin()->create();
    $other = User::factory()->admin()->create();

    actingAsApiUser($admin)
        ->postJson('/api/sites', [
            'description' => 'My Site',
            'active' => true,
            'user_id' => $other->id,
        ])
        ->assertCreated();

    $this->assertDatabaseHas('sites', [
        'description' => 'My Site',
        'user_id' => $admin->id,
    ]);

    $this->assertDatabaseMissing('sites', [
        'description' => 'My Site',
        'user_id' => $other->id,
    ]);
});
