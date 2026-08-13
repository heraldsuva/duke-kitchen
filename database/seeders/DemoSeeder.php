<?php

namespace Database\Seeders;

use App\Enums\EquipmentCondition;
use App\Enums\UserType;
use App\Models\Equipment;
use App\Models\RegisteredEquipment;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::query()->create([
            'user_type' => UserType::SuperAdmin,
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email_address' => 'superadmin@duke-kitchen.test',
            'user_name' => 'superadmin',
            'password' => Hash::make('Password1!'),
        ]);

        $adminOne = User::query()->create([
            'user_type' => UserType::Admin,
            'first_name' => 'Test',
            'last_name' => 'Admin1',
            'email_address' => 'admin1@duke-kitchen.test',
            'user_name' => 'admin1',
            'password' => Hash::make('Password1!'),
        ]);

        $adminTwo = User::query()->create([
            'user_type' => UserType::Admin,
            'first_name' => 'Test',
            'last_name' => 'Admin2',
            'email_address' => 'admin2@duke-kitchen.test',
            'user_name' => 'admin2',
            'password' => Hash::make('Password1!'),
        ]);

        $kfc = Site::query()->create([
            'user_id' => $adminOne->id,
            'description' => 'KFC Angeles',
            'active' => true,
        ]);

        $jollibee = Site::query()->create([
            'user_id' => $adminOne->id,
            'description' => 'Jollibee Marquee',
            'active' => true,
        ]);

        $smClark = Site::query()->create([
            'user_id' => $adminTwo->id,
            'description' => 'SM Clark',
            'active' => true,
        ]);

        Site::query()->create([
            'user_id' => $superAdmin->id,
            'description' => 'DSDC Demo Kitchen',
            'active' => true,
        ]);

        $ref001 = Equipment::query()->create([
            'serial_number' => 'REF-001',
            'description' => 'Refrigerator',
            'condition' => EquipmentCondition::Working,
            'user_id' => $adminOne->id,
        ]);

        $ref002 = Equipment::query()->create([
            'serial_number' => 'REF-002',
            'description' => 'Refrigerator',
            'condition' => EquipmentCondition::NotWorking,
            'user_id' => $adminOne->id,
        ]);

        $bld001 = Equipment::query()->create([
            'serial_number' => 'BLD-001',
            'description' => 'Blender',
            'condition' => EquipmentCondition::Working,
            'user_id' => $adminOne->id,
        ]);

        $ovn001 = Equipment::query()->create([
            'serial_number' => 'OVN-001',
            'description' => 'Oven',
            'condition' => EquipmentCondition::Working,
            'user_id' => $adminOne->id,
        ]);

        // Intentionally unregistered so Site Equipment / Edit Equipment can be demonstrated.
        Equipment::query()->create([
            'serial_number' => 'CHL-001',
            'description' => 'Chiller',
            'condition' => EquipmentCondition::Working,
            'user_id' => $adminOne->id,
        ]);

        $stv001 = Equipment::query()->create([
            'serial_number' => 'STV-001',
            'description' => 'Stove',
            'condition' => EquipmentCondition::NotWorking,
            'user_id' => $adminTwo->id,
        ]);

        $frz001 = Equipment::query()->create([
            'serial_number' => 'FRZ-001',
            'description' => 'Freezer',
            'condition' => EquipmentCondition::Working,
            'user_id' => $adminTwo->id,
        ]);

        RegisteredEquipment::query()->create([
            'equipment_id' => $ref001->id,
            'site_id' => $kfc->id,
        ]);

        RegisteredEquipment::query()->create([
            'equipment_id' => $bld001->id,
            'site_id' => $kfc->id,
        ]);

        RegisteredEquipment::query()->create([
            'equipment_id' => $ovn001->id,
            'site_id' => $jollibee->id,
        ]);

        RegisteredEquipment::query()->create([
            'equipment_id' => $ref002->id,
            'site_id' => $jollibee->id,
        ]);

        RegisteredEquipment::query()->create([
            'equipment_id' => $stv001->id,
            'site_id' => $smClark->id,
        ]);

        RegisteredEquipment::query()->create([
            'equipment_id' => $frz001->id,
            'site_id' => $smClark->id,
        ]);
    }
}
