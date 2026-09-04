<?php

namespace Database\Factories;

use App\Models\InstitutionSubscription;
use App\Models\SubscriptionPricingTier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubscriptionPricingTier>
 */
class SubscriptionPricingTierFactory extends Factory
{
    protected $model = SubscriptionPricingTier::class;

    public function definition(): array
    {
        $minStudents = fake()->numberBetween(1, 100);
        $maxStudents = fake()->optional(0.5)->numberBetween($minStudents + 1, $minStudents + 200);

        return [
            'institution_subscription_id' => InstitutionSubscription::factory(),
            'min_students' => $minStudents,
            'max_students' => $maxStudents,
            'price_per_student' => fake()->randomFloat(2, 5, 50),
        ];
    }

    public function tier1(): static
    {
        return $this->state(fn (array $attributes) => [
            'min_students' => 1,
            'max_students' => 100,
            'price_per_student' => 10.00,
        ]);
    }

    public function tier2(): static
    {
        return $this->state(fn (array $attributes) => [
            'min_students' => 101,
            'max_students' => 500,
            'price_per_student' => 8.00,
        ]);
    }

    public function tier3(): static
    {
        return $this->state(fn (array $attributes) => [
            'min_students' => 501,
            'max_students' => null,
            'price_per_student' => 5.00,
        ]);
    }
}
