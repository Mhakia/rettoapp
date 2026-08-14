<?php

namespace Database\Factories;

use App\Models\Challenge;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Challenge>
 */
class ChallengeFactory extends Factory
{
    protected $model = Challenge::class;

    public function definition(): array
    {
        return [
            'target_role' => fake()->randomElement(['student', 'teacher', 'guardian']),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'category' => fake()->randomElement(['inclusion', 'socioemotional_wellbeing', 'community']),
            'points' => fake()->numberBetween(10, 100),
            'difficulty' => fake()->randomElement(['easy', 'medium', 'hard']),
            'status' => 'published',
        ];
    }
}
