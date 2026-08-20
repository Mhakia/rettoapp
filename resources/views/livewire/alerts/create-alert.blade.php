<section class="mx-auto w-full max-w-2xl pb-28">
    <div class="mb-8">
        <flux:button variant="ghost" size="sm" icon="arrow-left" href="{{ route('alerts.index') }}" wire:navigate class="mb-4">
            {{ __('Volver') }}
        </flux:button>

        <div class="rounded-xl border border-teal-border bg-teal-bg px-6 py-5">
            <flux:heading size="xl" class="text-teal-deep!">{{ __('Nueva alerta') }}</flux:heading>
            <flux:text class="text-brand-text-muted!">
                {{ __('Registra una situación que requiere seguimiento sobre un estudiante.') }}
            </flux:text>
        </div>
    </div>

    <form wire:submit="store" class="space-y-6">
        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mb-5 flex items-start gap-3">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-teal-bg text-teal-deep">
                    <flux:icon icon="exclamation-triangle" variant="micro" class="size-5" />
                </span>
                <div>
                    <flux:heading size="lg">{{ __('Detalle de la alerta') }}</flux:heading>
                    <flux:text class="text-sm text-brand-text-muted!">{{ __('Selecciona el estudiante y describe la situación.') }}</flux:text>
                </div>
            </div>

            <div class="space-y-4">
                <flux:input wire:model.live.debounce.300ms="studentSearch" icon="magnifying-glass" :label="__('Buscar estudiante')" :placeholder="__('Nombre o número de documento...')" />

                <flux:select wire:model="student_id" :label="__('Estudiante')">
                    <flux:select.option value="">{{ __('Selecciona...') }}</flux:select.option>
                    @foreach ($this->students as $student)
                        <flux:select.option value="{{ $student->id }}">{{ $student->user->name }} ({{ $student->document_number }})</flux:select.option>
                    @endforeach
                </flux:select>

                <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                    <flux:input wire:model="type" :label="__('Tipo')" :placeholder="__('Ej. Ausentismo, bajo rendimiento...')" />
                    <flux:select wire:model="severity" :label="__('Severidad')">
                        <flux:select.option value="low">{{ __('Baja') }}</flux:select.option>
                        <flux:select.option value="medium">{{ __('Media') }}</flux:select.option>
                        <flux:select.option value="high">{{ __('Alta') }}</flux:select.option>
                    </flux:select>
                </div>

                <flux:textarea wire:model="message" :label="__('Descripción')" rows="4" />
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <flux:button variant="ghost" href="{{ route('alerts.index') }}" wire:navigate>{{ __('Cancelar') }}</flux:button>
            <flux:button variant="primary" type="submit">{{ __('Crear alerta') }}</flux:button>
        </div>
    </form>
</section>
