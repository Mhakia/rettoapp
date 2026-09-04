<?php

namespace App\Livewire\Billing;

use App\Models\Plan;
use Flux\Flux;
use Livewire\Attributes\Locked;
use Livewire\Component;

class PlanForm extends Component
{
    #[Locked]
    public ?Plan $plan = null;

    public string $name = '';

    public string $slug = '';

    public string $description = '';

    public float $basePrice = 0;

    public int $includedStudents = 0;

    public float $pricePerExtraStudent = 0;

    public string $billingCycle = 'monthly';

    public array $features = [];

    public function mount(?Plan $plan = null): void
    {
        $this->plan = $plan;

        if ($plan) {
            $this->name = $plan->name;
            $this->slug = $plan->slug;
            $this->description = $plan->description;
            $this->basePrice = $plan->base_price;
            $this->includedStudents = $plan->included_students;
            $this->pricePerExtraStudent = $plan->price_per_extra_student;
            $this->billingCycle = $plan->billing_cycle;
            $this->features = $plan->features ?? [];
        }
    }

    public function save(): void
    {
        if ($this->plan) {
            $this->authorize('update', $this->plan);
        } else {
            $this->authorize('create', Plan::class);
        }

        $rules = [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:plans,slug,'.($this->plan?->id ?? 'NULL'),
            'description' => 'nullable|string|max:1000',
            'basePrice' => 'required|numeric|min:0',
            'includedStudents' => 'required|integer|min:0',
            'pricePerExtraStudent' => 'required|numeric|min:0',
            'billingCycle' => 'required|in:monthly,quarterly,yearly',
            'features' => 'array',
        ];

        $data = $this->validate($rules);

        if ($this->plan) {
            $this->plan->update([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'],
                'base_price' => $data['basePrice'],
                'included_students' => $data['includedStudents'],
                'price_per_extra_student' => $data['pricePerExtraStudent'],
                'billing_cycle' => $data['billingCycle'],
                'features' => $data['features'],
            ]);
            Flux::toast()->success('Plan actualizado.');
        } else {
            Plan::create([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'],
                'base_price' => $data['basePrice'],
                'included_students' => $data['includedStudents'],
                'price_per_extra_student' => $data['pricePerExtraStudent'],
                'billing_cycle' => $data['billingCycle'],
                'features' => $data['features'],
            ]);
            Flux::toast()->success('Plan creado.');
        }

        $this->dispatch('plan-saved');
    }

    public function render()
    {
        return view('livewire.billing.plan-form');
    }
}
