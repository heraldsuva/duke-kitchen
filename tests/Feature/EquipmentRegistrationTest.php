<?php

use App\Enums\EquipmentCondition;
use App\Models\Equipment;
use App\Models\RegisteredEquipment;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\QueryException;

test('equipment cannot be registered to another users site', function () {
    $admin = User::factory()->admin()->create();
    $other = User::factory()->admin()->create();
    $otherSite = Site::factory()->create(['user_id' => $other->id]);

    $this->actingAs($admin)
        ->from(route('equipment.index'))
        ->post(route('equipment.store'), [
            'serial_number' => 'X-001',
            'description' => 'Blender',
            'condition' => EquipmentCondition::Working->value,
            'site_id' => $otherSite->id,
        ])
        ->assertRedirect(route('equipment.index'))
        ->assertSessionHasErrors('site_id');
});

test('equipment cannot have multiple simultaneous site registrations', function () {
    $user = User::factory()->admin()->create();
    $siteA = Site::factory()->create(['user_id' => $user->id]);
    $siteB = Site::factory()->create(['user_id' => $user->id]);
    $equipment = Equipment::factory()->create(['user_id' => $user->id]);

    RegisteredEquipment::query()->create([
        'equipment_id' => $equipment->id,
        'site_id' => $siteA->id,
    ]);

    expect(fn () => RegisteredEquipment::query()->create([
        'equipment_id' => $equipment->id,
        'site_id' => $siteB->id,
    ]))->toThrow(QueryException::class);
});

test('site can contain multiple equipment', function () {
    $user = User::factory()->admin()->create();
    $site = Site::factory()->create(['user_id' => $user->id]);
    $first = Equipment::factory()->create(['user_id' => $user->id]);
    $second = Equipment::factory()->create(['user_id' => $user->id]);

    RegisteredEquipment::query()->create([
        'equipment_id' => $first->id,
        'site_id' => $site->id,
    ]);

    RegisteredEquipment::query()->create([
        'equipment_id' => $second->id,
        'site_id' => $site->id,
    ]);

    expect($site->registeredEquipment()->count())->toBe(2);
});

test('editing equipment to another users site is rejected', function () {
    $admin = User::factory()->admin()->create();
    $other = User::factory()->admin()->create();
    $equipment = Equipment::factory()->create(['user_id' => $admin->id]);
    $otherSite = Site::factory()->create(['user_id' => $other->id]);

    $this->actingAs($admin)
        ->from(route('equipment.index'))
        ->put(route('equipment.update', $equipment), [
            'serial_number' => $equipment->serial_number,
            'description' => $equipment->description,
            'condition' => EquipmentCondition::Working->value,
            'site_id' => $otherSite->id,
        ])
        ->assertRedirect(route('equipment.index'))
        ->assertSessionHasErrors('site_id');
});

test('unregistering equipment is done by clearing site on edit', function () {
    $user = User::factory()->admin()->create();
    $site = Site::factory()->create(['user_id' => $user->id]);
    $equipment = Equipment::factory()->create(['user_id' => $user->id]);

    RegisteredEquipment::query()->create([
        'equipment_id' => $equipment->id,
        'site_id' => $site->id,
    ]);

    $this->actingAs($user)
        ->put(route('equipment.update', $equipment), [
            'serial_number' => $equipment->serial_number,
            'description' => $equipment->description,
            'condition' => EquipmentCondition::Working->value,
            'site_id' => null,
        ])
        ->assertRedirect(route('equipment.index'));

    $this->assertDatabaseMissing('registered_equipment', [
        'equipment_id' => $equipment->id,
    ]);
});
