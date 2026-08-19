<?php

use App\Models\Equipment;
use App\Models\RegisteredEquipment;
use App\Models\Site;
use App\Models\User;

test('user can create a site', function () {
    $user = User::factory()->admin()->create();

    actingAsApiUser($user)
        ->postJson('/api/sites', [
            'description' => 'KFC Angeles',
            'active' => true,
        ])
        ->assertCreated();

    $this->assertDatabaseHas('sites', [
        'description' => 'KFC Angeles',
        'user_id' => $user->id,
        'active' => true,
    ]);
});

test('user can edit their site', function () {
    $user = User::factory()->admin()->create();
    $site = Site::factory()->create(['user_id' => $user->id]);

    actingAsApiUser($user)
        ->putJson("/api/sites/{$site->id}", [
            'description' => 'Updated Site',
            'active' => false,
        ])
        ->assertOk();

    expect($site->fresh())
        ->description->toBe('Updated Site')
        ->active->toBeFalse();
});

test('deleting a site unregisters equipment but does not delete equipment', function () {
    $user = User::factory()->admin()->create();
    $site = Site::factory()->create(['user_id' => $user->id]);
    $equipment = Equipment::factory()->create(['user_id' => $user->id]);

    RegisteredEquipment::query()->create([
        'equipment_id' => $equipment->id,
        'site_id' => $site->id,
    ]);

    actingAsApiUser($user)
        ->deleteJson("/api/sites/{$site->id}")
        ->assertOk();

    $this->assertDatabaseMissing('sites', ['id' => $site->id]);
    $this->assertDatabaseMissing('registered_equipment', [
        'equipment_id' => $equipment->id,
    ]);
    $this->assertDatabaseHas('equipment', [
        'id' => $equipment->id,
        'serial_number' => $equipment->serial_number,
    ]);
});

test('user can delete a site with no equipment', function () {
    $user = User::factory()->admin()->create();
    $site = Site::factory()->create(['user_id' => $user->id]);

    actingAsApiUser($user)
        ->deleteJson("/api/sites/{$site->id}")
        ->assertOk();

    $this->assertDatabaseMissing('sites', ['id' => $site->id]);
});

test('super admin can assign site ownership', function () {
    $superAdmin = User::factory()->superAdmin()->create();
    $admin = User::factory()->admin()->create();

    actingAsApiUser($superAdmin)
        ->postJson('/api/sites', [
            'description' => 'Assigned Site',
            'active' => true,
            'user_id' => $admin->id,
        ])
        ->assertCreated();

    $this->assertDatabaseHas('sites', [
        'description' => 'Assigned Site',
        'user_id' => $admin->id,
    ]);
});
