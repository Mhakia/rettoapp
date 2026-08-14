<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'document_type' => fake()->randomElement(['registro_civil', 'tarjeta_identidad']),
            'document_number' => fake()->unique()->numerify('##########'),
            'birth_date' => fake()->dateTimeBetween('-17 years', '-5 years'),
        ];
    }
}
