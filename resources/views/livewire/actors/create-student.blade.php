<section class="mx-auto w-full max-w-3xl pb-28">
    <div class="mb-8">
        <flux:button variant="ghost" size="sm" icon="arrow-left" href="{{ $backUrl }}" wire:navigate class="mb-4">
            {{ __('Volver') }}
        </flux:button>

        <div class="rounded-xl border border-teal-border bg-teal-bg px-6 py-5">
            <flux:heading size="xl" class="text-teal-deep!">{{ __('Crear estudiante') }}</flux:heading>
            <flux:text class="text-brand-text-muted!">
                {{ __('Registra los datos del estudiante y asígnalo a su salón o grupo.') }}
            </flux:text>
            <flux:text class="mt-1 text-sm font-semibold text-teal-deep!">
                {{ __('Institución: :name', ['name' => $institutionName]) }}
            </flux:text>
        </div>

        <flux:text class="mt-3 text-sm text-brand-text-muted!">
            {{ __('¿Vas a crear varios estudiantes?') }}
            <flux:link href="{{ route('actors.students.import', ['institution' => $institutionUuid]) }}" wire:navigate>{{ __('Cárgalos desde un archivo de Excel') }}</flux:link>
        </flux:text>
    </div>

    <form wire:submit="store" class="space-y-6">
        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mb-5 flex items-start gap-3">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-teal-bg text-teal-deep">
                    <flux:icon icon="identification" variant="micro" class="size-5" />
                </span>
                <div>
                    <flux:heading size="lg">{{ __('Datos del estudiante') }}</flux:heading>
                    <flux:text class="text-sm text-brand-text-muted!">{{ __('Información personal del estudiante.') }}</flux:text>
                </div>
            </div>

            <div class="space-y-4">
                <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                    <flux:input wire:model="first_name" :label="__('Nombres')" />
                    <flux:input wire:model="last_name" :label="__('Apellidos')" />
                </div>
                <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                    <flux:select wire:model="document_type" :label="__('Tipo de documento')">
                        <flux:select.option value="">{{ __('Selecciona...') }}</flux:select.option>
                        <flux:select.option value="tarjeta_identidad">{{ __('Tarjeta de identidad (TI)') }}</flux:select.option>
                        <flux:select.option value="registro_civil">{{ __('Registro civil (RC)') }}</flux:select.option>
                    </flux:select>
                    <flux:input wire:model="document_number" :label="__('Número de documento')" />
                </div>
                <flux:input wire:model="birth_date" type="date" :label="__('Fecha de nacimiento')" />
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mb-5 flex items-start gap-3">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-teal-bg text-teal-deep">
                    <flux:icon icon="squares-2x2" variant="micro" class="size-5" />
                </span>
                <div>
                    <flux:heading size="lg">{{ __('Salón o grupo') }}</flux:heading>
                    <flux:text class="text-sm text-brand-text-muted!">{{ __('Un estudiante solo puede estar en un salón o grupo activo.') }}</flux:text>
                </div>
            </div>

            <flux:select wire:model="group_id" :label="__('Salón o grupo')">
                <flux:select.option value="">{{ __('Selecciona...') }}</flux:select.option>
                @foreach ($this->groups as $group)
                    <flux:select.option value="{{ $group->id }}">{{ $group->name }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <div class="flex justify-end gap-2">
            <flux:button variant="ghost" href="{{ $backUrl }}" wire:navigate>{{ __('Cancelar') }}</flux:button>
            <flux:button variant="primary" type="submit">{{ __('Crear estudiante') }}</flux:button>
        </div>
    </form>
</section>
