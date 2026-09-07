<?php

namespace App\Livewire\Billing;

use App\Models\Institution;
use App\Models\InstitutionSubscription;
use App\Models\Plan;
use Flux\Flux;
use Illuminate\Database\UniqueConstraintViolationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Suscripciones de instituciones')]
class SubscriptionForm extends Component
{
    #[Locked]
    public ?InstitutionSubscription $subscription = null;

    public string $institutionSearch = '';

    public ?int $institutionId = null;

    public string $institutionName = '';

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
        if ($subscription) {
            $this->authorize('update', $subscription);
        } else {
            $this->authorize('create', InstitutionSubscription::class);
        }

        $this->subscription = $subscription;

        if ($subscription) {
            $this->institutionId = $subscription->institution_id;
            $this->institutionName = $subscription->institution->name;
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

    /**
     * Matches while the admin is typing, capped so it never loads the full
     * institutions table (there can be thousands of them).
     */
    #[Computed]
    public function institutionResults()
    {
        $search = trim($this->institutionSearch);

        if ($search === '') {
            return collect();
        }

        return Institution::query()
            ->where(fn ($query) => $query->where('name', 'ilike', "%{$search}%")
                ->orWhere('nit', 'ilike', "%{$search}%"))
            ->orderBy('name')
            ->limit(8)
            ->get(['id', 'name', 'nit']);
    }

    public function selectInstitution(int $institutionId): void
    {
        $institution = Institution::findOrFail($institutionId);

        $this->institutionId = $institution->id;
        $this->institutionName = $institution->name;
        $this->institutionSearch = '';
        $this->resetErrorBag('institutionId');
    }

    public function clearInstitution(): void
    {
        $this->institutionId = null;
        $this->institutionName = '';
    }

    #[Computed]
    public function plans()
    {
        return Plan::orderBy('name')->get();
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

    public function updatedDiscountType(): void
    {
        if ($this->discountType === 'none') {
            $this->discountValue = 0;
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
            'discountValue' => $this->discountType === 'percentage'
                ? 'required|numeric|min:0|max:100'
                : 'required|numeric|min:0',
        ];

        $data = $this->validate($rules);

        try {
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
                Flux::toast()->success(__('billing_subscription_updated'));
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
                Flux::toast()->success(__('billing_subscription_created'));
            }
        } catch (UniqueConstraintViolationException) {
            // Partial unique index institution_subscriptions_one_active_per_institution.
            $this->addError('status', __('billing_subscription_duplicate_active'));

            return;
        }

        $this->redirectRoute('billing.subscriptions.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.billing.subscription-form');
    }
}
