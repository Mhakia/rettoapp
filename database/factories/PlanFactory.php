<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        $name = fake()->randomElement(['Basic', 'Professional', 'Enterprise', 'Plus', 'Standard']);
        $number = fake()->unique()->numberBetween(1, 999);

        return [
            'name' => "{$name} {$number}",
            'slug' => fake()->unique()->slug(2),
            'description' => fake()->sentence(),
            'base_price' => fake()->randomFloat(2, 50, 500),
            'included_students' => fake()->numberBetween(10, 100),
            'price_per_extra_student' => fake()->randomFloat(2, 5, 50),
            'billing_cycle' => fake()->randomElement(['monthly', 'quarterly', 'yearly']),
            'features' => [
                'dashboard' => true,
                'challenges' => true,
                'reports' => fake()->boolean(75),
                'api_access' => fake()->boolean(50),
                'priority_support' => fake()->boolean(30),
            ],
        ];
    }

    public function basic(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Basic',
            'slug' => 'basic',
            'base_price' => 99.99,
            'included_students' => 50,
            'price_per_extra_student' => 5.00,
        ]);
    }

    public function professional(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Professional',
            'slug' => 'professional',
            'base_price' => 299.99,
            'included_students' => 200,
            'price_per_extra_student' => 3.00,
            'features' => [
                'dashboard' => true,
                'challenges' => true,
                'reports' => true,
                'api_access' => true,
                'priority_support' => false,
            ],
        ]);
    }

    public function enterprise(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Enterprise',
            'slug' => 'enterprise',
            'base_price' => 999.99,
            'included_students' => 1000,
            'price_per_extra_student' => 1.00,
            'features' => [
                'dashboard' => true,
                'challenges' => true,
                'reports' => true,
                'api_access' => true,
                'priority_support' => true,
            ],
        ]);
    }
}
