<?php

namespace Database\Factories;

use App\Models\Marketer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Marketer>
 */
class MarketerFactory extends Factory
{
    protected $model = Marketer::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => fake()->unique()->numerify('9########'),
            'code' => Marketer::generateUniqueCode(),
            'base_commission_rate' => 5.00,
            'tier_increment_rate' => 1.00,
            'customers_per_tier' => 10,
            'is_active' => true,
        ];
    }
}
