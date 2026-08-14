<?php

namespace Database\Factories;

use App\Models\Institution;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Institution>
 */
class InstitutionFactory extends Factory
{
    protected $model = Institution::class;

    public function definition(): array
    {
        return [
            'name' => 'Institución Educativa '.fake()->city(),
            'nit' => fake()->unique()->numerify('900#######-#'),
            'address' => fake()->streetAddress(),
            'phone' => fake()->numerify('60########'),
            'bulletin_frequency' => fake()->randomElement(['weekly', 'biweekly', 'monthly', 'disabled']),
        ];
    }
}
