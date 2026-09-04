<?php

namespace Database\Factories;

use App\Models\InstitutionSubscription;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $subscription = InstitutionSubscription::factory()->create();
        $periodStart = fake()->dateTimeBetween('-3 months', 'now');
        $periodEnd = clone $periodStart;
        $periodEnd->modify('+1 month')->modify('-1 day');

        return [
            'institution_id' => $subscription->institution_id,
            'institution_subscription_id' => $subscription->id,
            'number' => 'INV-'.fake()->unique()->numerify('###############'),
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'status' => fake()->randomElement(['draft', 'sent', 'paid', 'cancelled']),
            'payment_method' => fake()->randomElement(['wompi', 'stripe', 'manual']),
            'total' => fake()->randomFloat(2, 500, 5000),
            'currency' => 'COP',
            'wompi_reference' => fake()->optional(0.5)->uuid(),
            'stripe_invoice_id' => fake()->optional(0.5)->uuid(),
            'notes' => fake()->optional(0.3)->sentence(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'payment_method' => 'manual',
        ]);
    }

    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'sent',
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
        ]);
    }

    public function forWompi(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'wompi',
            'wompi_reference' => fake()->uuid(),
            'stripe_invoice_id' => null,
        ]);
    }

    public function forStripe(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'stripe',
            'stripe_invoice_id' => fake()->uuid(),
            'wompi_reference' => null,
        ]);
    }
}
