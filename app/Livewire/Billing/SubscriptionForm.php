<?php

namespace App\Livewire\Billing;

use App\Models\Institution;
use App\Models\InstitutionSubscription;
use App\Models\Plan;
use Flux\Flux;
use Livewire\Attributes\Locked;
use Livewire\Component;

class SubscriptionForm extends Component
{
    #[Locked]
    public ?InstitutionSubscription $subscription = null;

    public ?int $institutionId = null;

    public ?int $planId = null;

    public ?int $contractId = null;

    public float $basePrice = 0;

    public int $includedStudents = 0;

    public float $pricePerExtraStudent = 0;

    public string $billingCycle = 'monthly';

    public string $status = 'active';

    public string $discountType = 'none';

    public float $discountValue = 0;

    public function mount(?InstitutionSubscription $subscription = null): void
    {
        $this->subscription = $subscription;

        if ($subscription) {
            $this->institutionId = $subscription->institution_id;
            $this->planId = $subscription->plan_id;
            $this->contractId = $subscription->contract_id;
            $this->basePrice = $subscription->base_price;
            $this->includedStudents = $subscription->included_students;
            $this->pricePerExtraStudent = $subscription->price_per_extra_student;
            $this->billingCycle = $subscription->billing_cycle;
            $this->status = $subscription->status;
            $this->discountType = $subscription->discount_type ?? 'none';
            $this->discountValue = $subscription->discount_value ?? 0;
        }
    }

    public function updatedPlanId(): void
    {
        if ($this->planId) {
            $plan = Plan::find($this->planId);
            if ($plan) {
                $this->basePrice = $plan->base_price;
                $this->includedStudents = $plan->included_students;
                $this->pricePerExtraStudent = $plan->price_per_extra_student;
                $this->billingCycle = $plan->billing_cycle;
            }
        }
    }

    public function save(): void
    {
        if ($this->subscription) {
            $this->authorize('update', $this->subscription);
        } else {
            $this->authorize('create', InstitutionSubscription::class);
        }

        $rules = [
            'institutionId' => 'required|exists:institutions,id',
            'planId' => 'nullable|exists:plans,id',
            'contractId' => 'nullable|exists:contracts,id',
            'basePrice' => 'required|numeric|min:0',
            'includedStudents' => 'required|integer|min:0',
            'pricePerExtraStudent' => 'required|numeric|min:0',
            'billingCycle' => 'required|in:monthly,quarterly,yearly',
            'status' => 'required|in:active,inactive,paused',
            'discountType' => 'required|in:none,fixed,percentage',
            'discountValue' => 'required|numeric|min:0',
        ];

        $data = $this->validate($rules);

        if ($this->subscription) {
            $this->subscription->update([
                'institution_id' => $data['institutionId'],
                'plan_id' => $data['planId'],
                'contract_id' => $data['contractId'],
                'base_price' => $data['basePrice'],
                'included_students' => $data['includedStudents'],
                'price_per_extra_student' => $data['pricePerExtraStudent'],
                'billing_cycle' => $data['billingCycle'],
                'status' => $data['status'],
                'discount_type' => $data['discountType'],
                'discount_value' => $data['discountValue'],
            ]);
            Flux::toast()->success('Suscripción actualizada.');
        } else {
            InstitutionSubscription::create([
                'institution_id' => $data['institutionId'],
                'plan_id' => $data['planId'],
                'contract_id' => $data['contractId'],
                'base_price' => $data['basePrice'],
                'included_students' => $data['includedStudents'],
                'price_per_extra_student' => $data['pricePerExtraStudent'],
                'billing_cycle' => $data['billingCycle'],
                'status' => $data['status'],
                'discount_type' => $data['discountType'],
                'discount_value' => $data['discountValue'],
                'started_at' => now(),
            ]);
            Flux::toast()->success('Suscripción creada.');
        }

        $this->dispatch('subscription-saved');
    }

    public function render()
    {
        return view('livewire.billing.subscription-form', [
            'institutions' => Institution::orderBy('name')->get(),
            'plans' => Plan::orderBy('name')->get(),
        ]);
    }
}
