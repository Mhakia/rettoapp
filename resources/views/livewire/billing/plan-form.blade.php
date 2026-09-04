<div class="space-y-6">
    <form wire:submit="save" class="space-y-6">
        <div class="grid gap-6 sm:grid-cols-2">
            <flux:input wire:model="name" :label="__('field_name')" required />
            <flux:input wire:model="slug" :label="__('field_slug')" required />
        </div>

        <flux:textarea wire:model="description" :label="__('field_description')" />

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

        <div class="flex gap-3">
            <flux:button type="submit" variant="primary">{{ __('action_save') }}</flux:button>
            <flux:button wire:click="$parent.closePlanForm()" variant="subtle">{{ __('action_cancel') }}</flux:button>
        </div>
    </form>
</div>
