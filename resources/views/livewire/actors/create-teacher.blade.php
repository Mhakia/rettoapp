<section class="mx-auto w-full max-w-3xl pb-28">
    <div class="mb-8">
        <flux:button variant="ghost" size="sm" icon="arrow-left" href="{{ $backUrl }}" wire:navigate class="mb-4">
            {{ __('Volver') }}
        </flux:button>

        <div class="rounded-xl border border-teal-border bg-teal-bg px-6 py-5">
            <flux:heading size="xl" class="text-teal-deep!">{{ __('Crear profesor') }}</flux:heading>
            <flux:text class="text-brand-text-muted!">
                {{ __('Registra los datos del profesor y asígnalo a los salones o grupos que estará a cargo.') }}
            </flux:text>
            <flux:text class="mt-1 text-sm font-semibold text-teal-deep!">
                {{ __('Institución: :name', ['name' => $institutionName]) }}
            </flux:text>
        </div>

        <flux:text class="mt-3 text-sm text-brand-text-muted!">
            {{ __('¿Vas a crear varios profesores?') }}
            <flux:link href="{{ route('actors.teachers.import', ['institution' => $institutionUuid]) }}" wire:navigate>{{ __('Cárgalos desde un archivo de Excel') }}</flux:link>
        </flux:text>
    </div>

    <form wire:submit="store" class="space-y-6">
        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mb-5 flex items-start gap-3">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-teal-bg text-teal-deep">
                    <flux:icon icon="identification" variant="micro" class="size-5" />
                </span>
                <div>
                    <flux:heading size="lg">{{ __('Datos del profesor') }}</flux:heading>
                    <flux:text class="text-sm text-brand-text-muted!">{{ __('Información personal y de contacto.') }}</flux:text>
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
                        <flux:select.option value="cedula_ciudadania">{{ __('Cédula de ciudadanía') }}</flux:select.option>
                        <flux:select.option value="cedula_extranjeria">{{ __('Cédula de extranjería') }}</flux:select.option>
                        <flux:select.option value="pasaporte">{{ __('Pasaporte') }}</flux:select.option>
                    </flux:select>
                    <flux:input wire:model="document_number" :label="__('Número de documento')" />
                </div>
                <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                    <flux:input wire:model="phone" :label="__('Celular')" />
                    <flux:input wire:model="email" type="email" :label="__('Correo (usuario de acceso)')" />
                </div>
                <flux:text class="text-sm text-brand-text-muted!">
                    {{ __('Se enviará un correo al profesor para que cree su propia contraseña, igual que al crear una institución.') }}
                </flux:text>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mb-5 flex items-start gap-3">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-teal-bg text-teal-deep">
                    <flux:icon icon="squares-2x2" variant="micro" class="size-5" />
                </span>
                <div>
                    <flux:heading size="lg">{{ __('Salones o grupos') }}</flux:heading>
                    <flux:text class="text-sm text-brand-text-muted!">{{ __('Puedes asignarle uno o varios salones. Podrás cambiarlos más adelante.') }}</flux:text>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-x-6 gap-y-1">
                @forelse ($this->groups as $group)
                    <label wire:key="group-{{ $group->id }}" class="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-1.5 select-none hover:bg-zinc-50 dark:hover:bg-zinc-800/60">
                        <flux:checkbox wire:model="group_ids" value="{{ $group->id }}" />
                        <span class="text-sm text-brand-text" x-on:click="$el.previousElementSibling.click()">{{ $group->name }}</span>
                    </label>
                @empty
                    <flux:text class="text-sm text-brand-text-muted!">{{ __('Tu institución aún no tiene salones o grupos creados.') }}</flux:text>
                @endforelse
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <flux:button variant="ghost" href="{{ $backUrl }}" wire:navigate>{{ __('Cancelar') }}</flux:button>
            <flux:button variant="primary" type="submit">{{ __('Crear profesor') }}</flux:button>
        </div>
    </form>
</section>
