<section class="w-full">
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4 rounded-xl border border-zinc-200 bg-white px-6 py-5">
        <div>
            <flux:heading size="xl">{{ __('Planes de suscripción') }}</flux:heading>
            <flux:text class="text-brand-text-muted!">{{ __('Catálogo de planes disponibles para la venta.') }}</flux:text>
        </div>

        @can('create', \App\Models\Plan::class)
            <flux:button variant="primary" icon="plus" href="#" wire:click="createPlan">{{ __('Crear plan') }}</flux:button>
        @endcan
    </div>

    <div class="mb-6">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" :placeholder="__('Buscar por nombre o slug...')" class="max-w-md" />
    </div>

    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm">
        <div class="divide-y divide-zinc-100">
            @forelse ($this->plans as $plan)
                <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4 hover:bg-zinc-50">
                    <div class="flex-1">
                        <flux:heading size="sm">{{ $plan->name }}</flux:heading>
                        <flux:text class="text-sm text-brand-text-muted!">{{ $plan->description }}</flux:text>
                        <div class="mt-2 flex gap-4 text-xs text-brand-text-muted!">
                            <span>{{ __('Base: :amount', ['amount' => number_format($plan->base_price, 2)]) }}</span>
                            <span>{{ __('Alumnos: :count', ['count' => $plan->included_students]) }}</span>
                            <span>{{ __('Extra: :amount', ['amount' => number_format($plan->price_per_extra_student, 2)]) }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        @can('update', $plan)
                            <flux:button size="sm" icon="pencil-square" :tooltip="__('Editar')" wire:click="editPlan({{ $plan->id }})" />
                        @endcan

                        @can('delete', $plan)
                            <flux:button size="sm" variant="danger" icon="trash" :tooltip="__('Eliminar')" wire:click="deletePlan({{ $plan->id }})" wire:confirm="{{ __('¿Eliminar este plan?') }}" />
                        @endcan
                    </div>
                </div>
            @empty
                <flux:text class="block px-6 py-10 text-center text-brand-text-muted!">{{ __('No se encontraron planes.') }}</flux:text>
            @endforelse
        </div>
    </div>

    @if ($this->plans->hasPages())
        <div class="mt-6">
            {{ $this->plans->links() }}
        </div>
    @endif

    @if ($this->showForm)
        <flux:modal name="plan-form" wire:click="closePlanForm()" :dismissible="false">
            <flux:heading>{{ $this->editingPlan ? __('Editar plan') : __('Crear plan') }}</flux:heading>
            <livewire:billing.plan-form :plan="$this->editingPlan" wire:key="plan-form-{{ $this->editingPlan?->id ?? 'new' }}" />
        </flux:modal>
    @endif
</section>
