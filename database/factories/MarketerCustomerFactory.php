<?php

namespace Database\Factories;

use App\Models\Marketer;
use App\Models\MarketerCustomer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MarketerCustomer>
 */
class MarketerCustomerFactory extends Factory
{
    protected $model = MarketerCustomer::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'marketer_id' => Marketer::factory(),
            'customer_name' => fake()->name(),
            'customer_phone' => fake()->unique()->numerify('9########'),
            'deal_amount' => null,
            'commission_rate' => 5.00,
            'commission_amount' => null,
            'notes' => null,
        ];
    }
}
