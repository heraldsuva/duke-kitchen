<?php

use App\Enums\EquipmentCondition;
use App\Models\Equipment;
use App\Models\RegisteredEquipment;
use App\Models\Site;
use App\Models\User;

test('user can create equipment with a site registration', function () {
    $user = User::factory()->admin()->create();
    $site = Site::factory()->create(['user_id' => $user->id]);

    actingAsApiUser($user)
        ->postJson('/api/equipment', [
            'serial_number' => 'REF-100',
            'description' => 'Refrigerator',
            'condition' => EquipmentCondition::Working->value,
            'site_id' => $site->id,
        ])
        ->assertCreated();

    $equipment = Equipment::query()->where('serial_number', 'REF-100')->first();

    expect($equipment)->not->toBeNull()
        ->and($equipment->user_id)->toBe($user->id);

    $this->assertDatabaseHas('registered_equipment', [
        'equipment_id' => $equipment->id,
        'site_id' => $site->id,
    ]);
});

test('user can create equipment without a site', function () {
    $user = User::factory()->admin()->create();

    actingAsApiUser($user)
        ->postJson('/api/equipment', [
            'serial_number' => 'CHL-100',
            'description' => 'Chiller',
            'condition' => EquipmentCondition::Working->value,
            'site_id' => null,
        ])
        ->assertCreated();

    $equipment = Equipment::query()->where('serial_number', 'CHL-100')->first();

    expect($equipment)->not->toBeNull();
    $this->assertDatabaseMissing('registered_equipment', [
        'equipment_id' => $equipment->id,
    ]);
});

test('user can edit equipment and change its site', function () {
    $user = User::factory()->admin()->create();
    $siteA = Site::factory()->create(['user_id' => $user->id, 'description' => 'Site A']);
    $siteB = Site::factory()->create(['user_id' => $user->id, 'description' => 'Site B']);
    $equipment = Equipment::factory()->create(['user_id' => $user->id]);

    RegisteredEquipment::query()->create([
        'equipment_id' => $equipment->id,
        'site_id' => $siteA->id,
    ]);

    actingAsApiUser($user)
        ->putJson("/api/equipment/{$equipment->id}", [
            'serial_number' => $equipment->serial_number,
            'description' => $equipment->description,
            'condition' => EquipmentCondition::Working->value,
            'site_id' => $siteB->id,
        ])
        ->assertOk();

    $this->assertDatabaseHas('registered_equipment', [
        'equipment_id' => $equipment->id,
        'site_id' => $siteB->id,
    ]);

    expect(RegisteredEquipment::query()->where('equipment_id', $equipment->id)->count())->toBe(1);
});

test('serial number must be unique', function () {
    $user = User::factory()->admin()->create();

    Equipment::factory()->create([
        'user_id' => $user->id,
        'serial_number' => 'REF-001',
    ]);

    actingAsApiUser($user)
        ->postJson('/api/equipment', [
            'serial_number' => 'REF-001',
            'description' => 'Duplicate',
            'condition' => EquipmentCondition::Working->value,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('serial_number');
});

test('deleting equipment removes its registration', function () {
    $user = User::factory()->admin()->create();
    $site = Site::factory()->create(['user_id' => $user->id]);
    $equipment = Equipment::factory()->create(['user_id' => $user->id]);

    RegisteredEquipment::query()->create([
        'equipment_id' => $equipment->id,
        'site_id' => $site->id,
    ]);

    actingAsApiUser($user)
        ->deleteJson("/api/equipment/{$equipment->id}")
        ->assertOk();

    $this->assertDatabaseMissing('equipment', ['id' => $equipment->id]);
    $this->assertDatabaseMissing('registered_equipment', [
        'equipment_id' => $equipment->id,
    ]);
});

test('condition must be working or not working', function () {
    $user = User::factory()->admin()->create();

    actingAsApiUser($user)
        ->postJson('/api/equipment', [
            'serial_number' => 'BAD-001',
            'description' => 'Broken Rule',
            'condition' => 'Broken',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('condition');
});
