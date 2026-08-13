<?php

namespace Database\Factories;

use App\Models\Equipment;
use App\Models\RegisteredEquipment;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RegisteredEquipment>
 */
class RegisteredEquipmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'equipment_id' => Equipment::factory(),
            'site_id' => Site::factory(),
        ];
    }
}
