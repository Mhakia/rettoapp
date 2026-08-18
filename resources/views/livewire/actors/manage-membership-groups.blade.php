<div>
    <flux:button
        size="sm"
        variant="ghost"
        icon="squares-2x2"
        :tooltip="__('Gestionar grupo')"
        x-on:click="$dispatch('modal-show', { name: 'manage-groups-{{ $membershipId }}' })"
    />

    <flux:modal name="manage-groups-{{ $membershipId }}" class="w-full max-w-md">
        <div class="space-y-4">
            <div class="flex items-center gap-3">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-teal-bg text-teal-deep">
                    <flux:icon icon="squares-2x2" variant="micro" class="size-5" />
                </span>
                <div>
                    <flux:heading size="lg">{{ __('Gestionar grupo') }}</flux:heading>
                    <flux:text class="text-sm text-brand-text-muted!">
                        {{ $isTeacher ? __('Vincula o desvincula al profesor de uno o varios grupos.') : __('Cambia el grupo asignado al estudiante.') }}
                    </flux:text>
                </div>
            </div>

            <form wire:submit="save" class="space-y-4">
                @if ($isTeacher)
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        @forelse ($this->groups as $group)
                            <flux:checkbox wire:model="group_ids" value="{{ $group->id }}" :label="$group->name" class="p-2" />
                        @empty
                            <flux:text class="text-sm text-brand-text-muted!">{{ __('Tu institución aún no tiene salones o grupos creados.') }}</flux:text>
                        @endforelse
                    </div>
                @else
                    <flux:select wire:model="group_id" :label="__('Grupo')">
                        <flux:select.option value="">{{ __('Selecciona...') }}</flux:select.option>
                        @foreach ($this->groups as $group)
                            <flux:select.option value="{{ $group->id }}">{{ $group->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                @endif

                <div class="flex justify-end gap-2">
                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('Cancelar') }}</flux:button>
                    </flux:modal.close>
                    <flux:button variant="primary" type="submit">{{ __('Guardar') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
