<?php

namespace Database\Seeders;

use App\Models\Contract;
use App\Models\Institution;
use App\Models\InstitutionSubscription;
use App\Models\Plan;
use App\Models\SubscriptionAddon;
use App\Models\SubscriptionPricingTier;
use Illuminate\Database\Seeder;

class BillingSeeder extends Seeder
{
    /**
     * Seed example billing data: plans, contracts, and sample subscriptions.
     * Demonstrates how pricing tiers and addons work.
     */
    public function run(): void
    {
        // Create three standard plans for public catalog
        $basicPlan = Plan::firstOrCreate(
            ['slug' => 'basic'],
            [
                'name' => 'Basic',
                'description' => 'Perfecto para instituciones pequeñas',
                'base_price' => 99.99,
                'included_students' => 50,
                'price_per_extra_student' => 5.00,
                'billing_cycle' => 'monthly',
                'features' => [
                    'dashboard' => true,
                    'challenges' => true,
                    'reports' => false,
                    'api_access' => false,
                    'priority_support' => false,
                ],
            ]
        );

        $professionalPlan = Plan::firstOrCreate(
            ['slug' => 'professional'],
            [
                'name' => 'Professional',
                'description' => 'Para instituciones medianas',
                'base_price' => 299.99,
                'included_students' => 200,
                'price_per_extra_student' => 3.00,
                'billing_cycle' => 'monthly',
                'features' => [
                    'dashboard' => true,
                    'challenges' => true,
                    'reports' => true,
                    'api_access' => true,
                    'priority_support' => false,
                ],
            ]
        );

        $enterprisePlan = Plan::firstOrCreate(
            ['slug' => 'enterprise'],
            [
                'name' => 'Enterprise',
                'description' => 'Para grandes redes de instituciones',
                'base_price' => 999.99,
                'included_students' => 1000,
                'price_per_extra_student' => 1.00,
                'billing_cycle' => 'monthly',
                'features' => [
                    'dashboard' => true,
                    'challenges' => true,
                    'reports' => true,
                    'api_access' => true,
                    'priority_support' => true,
                ],
            ]
        );

        // Create a sample government contract (convenio)
        $departmentContract = Contract::firstOrCreate(
            ['name' => 'Convenio Secretaría de Educación Cundinamarca'],
            [
                'type' => 'departmental_agreement',
                'entity_name' => 'Secretaría de Educación Cundinamarca',
                'default_price_per_student' => 8.50,
                'default_included_students' => 150,
                'negotiated_by' => 'Commercial Team',
                'status' => 'active',
                'starts_at' => now()->subYear(),
                'ends_at' => now()->addYear(),
                'notes' => 'Convenio departamental para 50 instituciones educativas públicas',
            ]
        );

        // Assign sample subscriptions to existing institutions
        $institutions = Institution::all();

        if ($institutions->isEmpty()) {
            $this->command->info('No institutions found. Skipping subscriptions.');

            return;
        }

        // First institution: Basic plan with no extra features
        if ($institutions->count() >= 1) {
            $subscription1 = InstitutionSubscription::firstOrCreate(
                ['institution_id' => $institutions[0]->id, 'status' => 'active'],
                [
                    'plan_id' => $basicPlan->id,
                    'base_price' => 99.99,
                    'included_students' => 50,
                    'price_per_extra_student' => 5.00,
                    'discount_type' => 'none',
                    'discount_value' => 0,
                    'billing_cycle' => 'monthly',
                    'features' => $basicPlan->features,
                    'status' => 'active',
                    'started_at' => now()->subMonths(3),
                ]
            );
            $this->command->info("✓ Subscription created for {$institutions[0]->name}");
        }

        // Second institution: Professional plan with discount and tiered pricing
        if ($institutions->count() >= 2) {
            $subscription2 = InstitutionSubscription::firstOrCreate(
                ['institution_id' => $institutions[1]->id, 'status' => 'active'],
                [
                    'plan_id' => $professionalPlan->id,
                    'base_price' => 269.99,
                    'included_students' => 200,
                    'price_per_extra_student' => 3.00,
                    'discount_type' => 'percentage',
                    'discount_value' => 10,
                    'billing_cycle' => 'monthly',
                    'features' => $professionalPlan->features,
                    'status' => 'active',
                    'started_at' => now()->subMonths(6),
                ]
            );

            // Add pricing tiers (volume discounts for extra students)
            SubscriptionPricingTier::firstOrCreate(
                ['institution_subscription_id' => $subscription2->id, 'min_students' => 201],
                [
                    'min_students' => 201,
                    'max_students' => 400,
                    'price_per_student' => 2.50,
                ]
            );

            SubscriptionPricingTier::firstOrCreate(
                ['institution_subscription_id' => $subscription2->id, 'min_students' => 401],
                [
                    'min_students' => 401,
                    'max_students' => null,
                    'price_per_student' => 2.00,
                ]
            );

            // Add recurring addon
            SubscriptionAddon::firstOrCreate(
                ['institution_subscription_id' => $subscription2->id, 'key' => 'priority_support'],
                [
                    'name' => 'Priority Support',
                    'price' => 99.99,
                    'billing_cycle' => 'monthly',
                ]
            );

            $this->command->info("✓ Subscription with tiers and addon created for {$institutions[1]->name}");
        }

        // Third institution: Government contract subscription
        if ($institutions->count() >= 3) {
            $subscription3 = InstitutionSubscription::firstOrCreate(
                ['institution_id' => $institutions[2]->id, 'status' => 'active'],
                [
                    'contract_id' => $departmentContract->id,
                    'base_price' => $departmentContract->default_price_per_student * $departmentContract->default_included_students,
                    'included_students' => $departmentContract->default_included_students,
                    'price_per_extra_student' => $departmentContract->default_price_per_student,
                    'discount_type' => 'none',
                    'discount_value' => 0,
                    'billing_cycle' => 'monthly',
                    'status' => 'active',
                    'started_at' => $departmentContract->starts_at,
                ]
            );

            $this->command->info("✓ Government contract subscription created for {$institutions[2]->name}");
        }

        $this->command->info('✓ Billing demo data seeded successfully!');
    }
}
