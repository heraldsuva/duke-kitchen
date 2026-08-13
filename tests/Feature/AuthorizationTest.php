<?php

use App\Models\Equipment;
use App\Models\Site;
use App\Models\User;

test('admin cannot access user maintenance', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('users.index'))
        ->assertForbidden();
});

test('super admin can access user maintenance', function () {
    $superAdmin = User::factory()->superAdmin()->create();

    $this->actingAs($superAdmin)
        ->get(route('users.index'))
        ->assertOk();
});

test('admin cannot access another users site', function () {
    $admin = User::factory()->admin()->create();
    $other = User::factory()->admin()->create();
    $site = Site::factory()->create(['user_id' => $other->id]);

    $this->actingAs($admin)
        ->get(route('sites.equipment', $site))
        ->assertForbidden();
});

test('admin cannot update another users site', function () {
    $admin = User::factory()->admin()->create();
    $other = User::factory()->admin()->create();
    $site = Site::factory()->create(['user_id' => $other->id]);

    $this->actingAs($admin)
        ->put(route('sites.update', $site), [
            'description' => 'Hacked Site',
            'active' => true,
        ])
        ->assertForbidden();
});

test('admin cannot access another users equipment', function () {
    $admin = User::factory()->admin()->create();
    $other = User::factory()->admin()->create();
    $equipment = Equipment::factory()->create(['user_id' => $other->id]);

    $this->actingAs($admin)
        ->put(route('equipment.update', $equipment), [
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

    $this->actingAs($admin)
        ->post(route('sites.store'), [
            'description' => 'My Site',
            'active' => true,
            'user_id' => $other->id,
        ])
        ->assertRedirect(route('sites.index'));

    $this->assertDatabaseHas('sites', [
        'description' => 'My Site',
        'user_id' => $admin->id,
    ]);

    $this->assertDatabaseMissing('sites', [
        'description' => 'My Site',
        'user_id' => $other->id,
    ]);
});
