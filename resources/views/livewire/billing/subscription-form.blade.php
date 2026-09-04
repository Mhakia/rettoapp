<div class="space-y-6">
    <form wire:submit="save" class="space-y-6">
        <flux:select wire:model="institutionId" :label="__('field_institution')" required>
            <flux:select.option value="">{{ __('billing_select_institution') }}</flux:select.option>
            @foreach ($institutions as $institution)
                <flux:select.option value="{{ $institution->id }}">{{ $institution->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model="planId" :label="__('billing_plan_optional')" @change="$wire.updatedPlanId()">
            <flux:select.option value="">{{ __('option_none') }}</flux:select.option>
            @foreach ($plans as $plan)
                <flux:select.option value="{{ $plan->id }}">{{ $plan->name }} ({{ number_format($plan->base_price, 2) }})</flux:select.option>
            @endforeach
        </flux:select>

        <div class="grid gap-6 sm:grid-cols-2">
            <flux:input wire:model.number="basePrice" type="number" step="0.01" :label="__('billing_plan_base_price')" required />
            <flux:input wire:model.number="includedStudents" type="number" :label="__('billing_plan_included_students')" required />
            <flux:input wire:model.number="pricePerExtraStudent" type="number" step="0.01" :label="__('billing_plan_extra_student_price')" required />
            <flux:select wire:model="billingCycle" :label="__('billing_plan_billing_cycle')">
                <flux:select.option value="monthly">{{ __('frequency_monthly') }}</flux:select.option>
                <flux:select.option value="quarterly">{{ __('frequency_quarterly') }}</flux:select.option>
                <flux:select.option value="yearly">{{ __('frequency_yearly') }}</flux:select.option>
            </flux:select>
        </div>

        <flux:select wire:model="status" :label="__('field_status')">
            <flux:select.option value="active">{{ __('subscription_status_active') }}</flux:select.option>
            <flux:select.option value="inactive">{{ __('subscription_status_inactive') }}</flux:select.option>
            <flux:select.option value="paused">{{ __('subscription_status_paused') }}</flux:select.option>
        </flux:select>

        <div class="grid gap-6 sm:grid-cols-2">
            <flux:select wire:model="discountType" :label="__('billing_discount_type')">
                <flux:select.option value="none">{{ __('option_none') }}</flux:select.option>
                <flux:select.option value="fixed">{{ __('billing_discount_fixed') }}</flux:select.option>
                <flux:select.option value="percentage">{{ __('billing_discount_percentage') }}</flux:select.option>
            </flux:select>
            <flux:input wire:model.number="discountValue" type="number" step="0.01" :label="__('billing_discount_value')" required />
        </div>

        <div class="flex gap-3">
            <flux:button type="submit" variant="primary">{{ __('action_save') }}</flux:button>
            <flux:button wire:click="$parent.closeSubscriptionForm()" variant="subtle">{{ __('action_cancel') }}</flux:button>
        </div>
    </form>
</div>
