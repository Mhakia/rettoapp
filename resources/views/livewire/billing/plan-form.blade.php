<div class="space-y-6">
    <form wire:submit="save" class="space-y-6">
        <div class="grid gap-6 sm:grid-cols-2">
            <flux:input wire:model="name" :label="__('Nombre')" required />
            <flux:input wire:model="slug" :label="__('Slug')" required />
        </div>

        <flux:textarea wire:model="description" :label="__('Descripción')" />

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

        <div class="flex gap-3">
            <flux:button type="submit" variant="primary">{{ __('Guardar') }}</flux:button>
            <flux:button wire:click="$parent.closePlanForm()" variant="subtle">{{ __('Cancelar') }}</flux:button>
        </div>
    </form>
</div>
