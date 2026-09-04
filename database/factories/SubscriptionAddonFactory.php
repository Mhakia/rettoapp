<?php

namespace Database\Factories;

use App\Models\InstitutionSubscription;
use App\Models\SubscriptionAddon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubscriptionAddon>
 */
class SubscriptionAddonFactory extends Factory
{
    protected $model = SubscriptionAddon::class;

    public function definition(): array
    {
        return [
            'institution_subscription_id' => InstitutionSubscription::factory(),
            'key' => fake()->slug(1),
            'name' => fake()->randomElement([
                'Priority Support',
                'Advanced Analytics',
                'API Access',
                'Custom Integrations',
                'Dedicated Account Manager',
                'White-label Solution',
            ]),
            'price' => fake()->randomFloat(2, 50, 500),
            'billing_cycle' => fake()->randomElement([null, 'monthly', 'quarterly', 'yearly']),
        ];
    }

    public function recurring(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_cycle' => fake()->randomElement(['monthly', 'quarterly', 'yearly']),
        ]);
    }

    public function oneTime(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_cycle' => null,
        ]);
    }
}
