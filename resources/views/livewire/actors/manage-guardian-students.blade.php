<div>
    <flux:button
        size="sm"
        variant="ghost"
        icon="link"
        :tooltip="__('Vincular o desvincular estudiantes')"
        x-on:click="$dispatch('modal-show', { name: 'manage-guardian-{{ $guardianId }}' })"
    />

    <flux:modal name="manage-guardian-{{ $guardianId }}" :dismissible="false" class="w-full max-w-lg">
        <div class="space-y-4">
            <div class="flex items-center gap-3">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-teal-bg text-teal-deep">
                    <flux:icon icon="link" variant="micro" class="size-5" />
                </span>
                <div>
                    <flux:heading size="lg">{{ __('Vincular estudiantes') }}</flux:heading>
                    <flux:text class="text-sm text-brand-text-muted!">
                        {{ __('Marca o desmarca los estudiantes de tu institución que debe tener asignados este acudiente.') }}
                    </flux:text>
                </div>
            </div>

            <form wire:submit="save" class="space-y-4">
                <flux:input wire:model.live.debounce.300ms="studentSearch" icon="magnifying-glass" :placeholder="__('Buscar por nombre o número de documento...')" />

                <div class="max-h-72 space-y-1 overflow-y-auto rounded-lg border border-zinc-200 p-2 dark:border-zinc-700">
                    @forelse ($this->students as $student)
                        <label wire:key="guardian-{{ $guardianId }}-student-{{ $student->id }}" class="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-1.5 select-none hover:bg-zinc-50 dark:hover:bg-zinc-800/60">
                            <flux:checkbox wire:model="student_ids" value="{{ $student->id }}" />
                            <span class="text-sm text-brand-text" x-on:click="$el.previousElementSibling.click()">{{ $student->user->name }}</span>
                        </label>
                    @empty
                        <flux:text class="p-2 text-sm text-brand-text-muted!">{{ __('No se encontraron estudiantes.') }}</flux:text>
                    @endforelse
                </div>

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
