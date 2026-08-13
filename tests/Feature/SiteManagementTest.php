<?php

use App\Models\Equipment;
use App\Models\RegisteredEquipment;
use App\Models\Site;
use App\Models\User;

test('user can create a site', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->post(route('sites.store'), [
            'description' => 'KFC Angeles',
            'active' => true,
        ])
        ->assertRedirect(route('sites.index'));

    $this->assertDatabaseHas('sites', [
        'description' => 'KFC Angeles',
        'user_id' => $user->id,
        'active' => true,
    ]);
});

test('user can edit their site', function () {
    $user = User::factory()->admin()->create();
    $site = Site::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->put(route('sites.update', $site), [
            'description' => 'Updated Site',
            'active' => false,
        ])
        ->assertRedirect(route('sites.index'));

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

    $this->actingAs($user)
        ->delete(route('sites.destroy', $site))
        ->assertRedirect(route('sites.index'));

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

    $this->actingAs($user)
        ->delete(route('sites.destroy', $site))
        ->assertRedirect(route('sites.index'));

    $this->assertDatabaseMissing('sites', ['id' => $site->id]);
});

test('super admin can assign site ownership', function () {
    $superAdmin = User::factory()->superAdmin()->create();
    $admin = User::factory()->admin()->create();

    $this->actingAs($superAdmin)
        ->post(route('sites.store'), [
            'description' => 'Assigned Site',
            'active' => true,
            'user_id' => $admin->id,
        ])
        ->assertRedirect(route('sites.index'));

    $this->assertDatabaseHas('sites', [
        'description' => 'Assigned Site',
        'user_id' => $admin->id,
    ]);
});
