<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'description' => fake()->randomElement([
                'Base subscription fee',
                'Additional students',
                'Premium support',
                'Advanced analytics',
                'API access',
                'Custom integration',
            ]),
            'quantity' => fake()->numberBetween(1, 100),
            'unit_price' => fake()->randomFloat(2, 5, 100),
            'amount' => 0, // Will be calculated by the model
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (InvoiceItem $item) {
            $item->amount = $item->quantity * $item->unit_price;
        })->afterCreating(function (InvoiceItem $item) {
            $item->amount = $item->quantity * $item->unit_price;
            $item->save();
        });
    }

    public function basePrice(): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => 'Base subscription fee',
            'quantity' => 1,
            'unit_price' => fake()->randomFloat(2, 100, 1000),
        ]);
    }

    public function additionalStudents(): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => 'Additional students',
            'quantity' => fake()->numberBetween(1, 100),
            'unit_price' => fake()->randomFloat(2, 5, 50),
        ]);
    }

    public function addon(): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => fake()->randomElement([
                'Premium support',
                'Advanced analytics',
                'API access',
                'Custom integration',
            ]),
            'quantity' => 1,
            'unit_price' => fake()->randomFloat(2, 50, 500),
        ]);
    }
}
