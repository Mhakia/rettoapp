<div class="space-y-6">
    <form wire:submit="save" class="space-y-6">
        <flux:select wire:model="institutionId" :label="__('Institución')" required>
            <flux:select.option value="">{{ __('Selecciona una institución') }}</flux:select.option>
            @foreach ($institutions as $institution)
                <flux:select.option value="{{ $institution->id }}">{{ $institution->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model="planId" :label="__('Plan (opcional)')" @change="$wire.updatedPlanId()">
            <flux:select.option value="">{{ __('Ninguno') }}</flux:select.option>
            @foreach ($plans as $plan)
                <flux:select.option value="{{ $plan->id }}">{{ $plan->name }} ({{ number_format($plan->base_price, 2) }})</flux:select.option>
            @endforeach
        </flux:select>

        <div class="grid gap-6 sm:grid-cols-2">
            <flux:input wire:model.number="basePrice" type="number" step="0.01" :label="__('Precio base')" required />
            <flux:input wire:model.number="includedStudents" type="number" :label="__('Alumnos incluidos')" required />
            <flux:input wire:model.number="pricePerExtraStudent" type="number" step="0.01" :label="__('Precio por alumno extra')" required />
            <flux:select wire:model="billingCycle" :label="__('Ciclo de facturación')">
                <flux:select.option value="monthly">{{ __('Mensual') }}</flux:select.option>
                <flux:select.option value="quarterly">{{ __('Trimestral') }}</flux:select.option>
                <flux:select.option value="yearly">{{ __('Anual') }}</flux:select.option>
            </flux:select>
        </div>

        <flux:select wire:model="status" :label="__('Estado')">
            <flux:select.option value="active">{{ __('Activa') }}</flux:select.option>
            <flux:select.option value="inactive">{{ __('Inactiva') }}</flux:select.option>
            <flux:select.option value="paused">{{ __('Pausada') }}</flux:select.option>
        </flux:select>

        <div class="grid gap-6 sm:grid-cols-2">
            <flux:select wire:model="discountType" :label="__('Tipo de descuento')">
                <flux:select.option value="none">{{ __('Ninguno') }}</flux:select.option>
                <flux:select.option value="fixed">{{ __('Monto fijo') }}</flux:select.option>
                <flux:select.option value="percentage">{{ __('Porcentaje') }}</flux:select.option>
            </flux:select>
            <flux:input wire:model.number="discountValue" type="number" step="0.01" :label="__('Valor de descuento')" required />
        </div>

        <div class="flex gap-3">
            <flux:button type="submit" variant="primary">{{ __('Guardar') }}</flux:button>
            <flux:button wire:click="$parent.closeSubscriptionForm()" variant="subtle">{{ __('Cancelar') }}</flux:button>
        </div>
    </form>
</div>
