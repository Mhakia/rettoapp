<?php

namespace Database\Factories;

use App\Models\Contract;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contract>
 */
class ContractFactory extends Factory
{
    protected $model = Contract::class;

    public function definition(): array
    {
        return [
            'name' => 'Convenio '.fake()->company(),
            'type' => fake()->randomElement(['standard', 'corporate', 'departmental_agreement', 'national_agreement']),
            'entity_name' => fake()->optional(0.7)->company(),
            'default_price_per_student' => fake()->randomFloat(2, 10, 100),
            'default_included_students' => fake()->numberBetween(100, 500),
            'negotiated_by' => fake()->name(),
            'status' => fake()->randomElement(['active', 'expired', 'cancelled']),
            'starts_at' => fake()->dateTimeBetween('-2 years', 'now'),
            'ends_at' => fake()->optional(0.3)->dateTimeBetween('now', '+2 years'),
            'notes' => fake()->optional(0.5)->sentence(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'ends_at' => fake()->dateTimeBetween('now', '+2 years'),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'ends_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ]);
    }
}
