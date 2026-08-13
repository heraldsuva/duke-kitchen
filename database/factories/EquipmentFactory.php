<?php

namespace Database\Factories;

use App\Enums\EquipmentCondition;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Equipment>
 */
class EquipmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'serial_number' => strtoupper(fake()->unique()->bothify('???-###')),
            'description' => fake()->randomElement([
                'Refrigerator',
                'Blender',
                'Chiller',
                'Freezer',
                'Oven',
                'Stove',
            ]),
            'condition' => fake()->randomElement(EquipmentCondition::cases()),
            'user_id' => User::factory(),
        ];
    }

    public function working(): static
    {
        return $this->state(fn (array $attributes) => [
            'condition' => EquipmentCondition::Working,
        ]);
    }

    public function notWorking(): static
    {
        return $this->state(fn (array $attributes) => [
            'condition' => EquipmentCondition::NotWorking,
        ]);
    }
}
