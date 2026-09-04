<?php

namespace Database\Factories;

use App\Models\Contract;
use App\Models\Institution;
use App\Models\InstitutionSubscription;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InstitutionSubscription>
 */
class InstitutionSubscriptionFactory extends Factory
{
    protected $model = InstitutionSubscription::class;

    public function definition(): array
    {
        $plan = fake()->boolean(70) ? Plan::factory()->create() : null;
        $contract = fake()->boolean(30) ? Contract::factory()->create() : null;

        return [
            'institution_id' => Institution::factory(),
            'plan_id' => $plan?->id,
            'contract_id' => $contract?->id,
            'base_price' => fake()->randomFloat(2, 100, 1000),
            'included_students' => fake()->numberBetween(50, 200),
            'price_per_extra_student' => fake()->randomFloat(2, 5, 50),
            'discount_type' => fake()->randomElement(['none', 'fixed', 'percentage']),
            'discount_value' => fake()->optional(0.7)->randomFloat(2, 0, 50),
            'billing_cycle' => fake()->randomElement(['monthly', 'quarterly', 'yearly']),
            'features' => [
                'dashboard' => true,
                'challenges' => true,
                'reports' => fake()->boolean(),
            ],
            'status' => fake()->randomElement(['active', 'inactive', 'paused']),
            'started_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'ended_at' => fake()->optional(0.2)->dateTimeBetween('now', '+1 year'),
            'notes' => fake()->optional(0.3)->sentence(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'ended_at' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
            'ended_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ]);
    }

    public function withPlan(): static
    {
        return $this->state(fn (array $attributes) => [
            'plan_id' => Plan::factory(),
            'contract_id' => null,
        ]);
    }

    public function withContract(): static
    {
        return $this->state(fn (array $attributes) => [
            'contract_id' => Contract::factory(),
            'plan_id' => null,
        ]);
    }
}
