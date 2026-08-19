<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (UserType::cases() as $type) {
            Role::query()->firstOrCreate(['name' => $type->value]);
        }
    }
}
