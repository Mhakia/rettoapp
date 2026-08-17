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
            'contact_first_name' => fake()->firstName(),
            'contact_middle_name' => fake()->optional()->firstName(),
            'contact_last_name' => fake()->lastName(),
            'contact_second_last_name' => fake()->lastName(),
            'contact_document_type' => fake()->randomElement(['cedula_ciudadania', 'cedula_extranjeria', 'pasaporte']),
            'contact_document_number' => fake()->unique()->numerify('##########'),
            'contact_email' => fake()->unique()->safeEmail(),
            'contact_phone' => fake()->numerify('3##########'),
            'principal_name' => fake()->name(),
            'principal_document_type' => fake()->randomElement(['cedula_ciudadania', 'cedula_extranjeria', 'pasaporte']),
            'principal_document_number' => fake()->unique()->numerify('##########'),
            'principal_started_at' => fake()->optional()->date(),
            'country' => 'Colombia',
            'state' => fake()->state(),
            'city' => fake()->city(),
            'entity_type' => fake()->randomElement(['public', 'private']),
        ];
    }
}
