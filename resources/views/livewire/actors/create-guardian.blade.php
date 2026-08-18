<section class="mx-auto w-full max-w-3xl pb-28">
    <div class="mb-8">
        <flux:button variant="ghost" size="sm" icon="arrow-left" href="{{ $backUrl }}" wire:navigate class="mb-4">
            {{ __('Volver') }}
        </flux:button>

        <div class="rounded-xl border border-teal-border bg-teal-bg px-6 py-5">
            <flux:heading size="xl" class="text-teal-deep!">{{ __('Crear acudiente') }}</flux:heading>
            <flux:text class="text-brand-text-muted!">
                {{ __('Registra un acudiente y vincúlalo a uno o varios estudiantes de tu institución.') }}
            </flux:text>
            <flux:text class="mt-1 text-sm font-semibold text-teal-deep!">
                {{ __('Institución: :name', ['name' => $institutionName]) }}
            </flux:text>
        </div>
    </div>

    <form wire:submit="store" class="space-y-6">
        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mb-5 flex items-start gap-3">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-teal-bg text-teal-deep">
                    <flux:icon icon="identification" variant="micro" class="size-5" />
                </span>
                <div>
                    <flux:heading size="lg">{{ __('Datos del acudiente') }}</flux:heading>
                    <flux:text class="text-sm text-brand-text-muted!">{{ __('Información personal y de contacto.') }}</flux:text>
                </div>
            </div>

            <div class="space-y-4">
                <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                    <flux:select wire:model.live="document_type" :label="__('Tipo de documento')">
                        <flux:select.option value="">{{ __('Selecciona...') }}</flux:select.option>
                        <flux:select.option value="cedula_ciudadania">{{ __('Cédula de ciudadanía') }}</flux:select.option>
                        <flux:select.option value="cedula_extranjeria">{{ __('Cédula de extranjería') }}</flux:select.option>
                        <flux:select.option value="pasaporte">{{ __('Pasaporte') }}</flux:select.option>
                    </flux:select>
                    <flux:input wire:model.live.debounce.500ms="document_number" :label="__('Número de documento')" />
                </div>

                @if ($existingGuardian)
                    <div class="flex items-start gap-3 rounded-lg bg-amber-bg px-4 py-3">
                        <flux:icon icon="information-circle" variant="micro" class="mt-0.5 size-5 shrink-0 text-amber" />
                        <div class="text-sm">
                            <p class="font-semibold text-amber">{{ __('Este acudiente ya está registrado en la plataforma.') }}</p>
                            <p class="text-brand-text-muted!">{{ __('Sus datos no se pueden editar aquí; solo puedes vincularlo a estudiantes de tu institución.') }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3 rounded-lg bg-zinc-50 p-4 text-sm sm:grid-cols-2 dark:bg-zinc-800/50">
                        <div>
                            <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Nombre') }}</div>
                            <div class="font-medium text-brand-text">{{ $existingGuardian['name'] }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Correo') }}</div>
                            <div class="font-medium text-brand-text">{{ $existingGuardian['email'] }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Celular') }}</div>
                            <div class="font-medium text-brand-text">{{ $existingGuardian['phone'] ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Fecha de nacimiento') }}</div>
                            <div class="font-medium text-brand-text">{{ $existingGuardian['birth_date'] ?? '—' }}</div>
                        </div>
                        <div class="sm:col-span-2">
                            <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Dirección') }}</div>
                            <div class="font-medium text-brand-text">{{ $existingGuardian['address'] ?? '—' }}</div>
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                        <flux:input wire:model="first_name" :label="__('Nombres')" />
                        <flux:input wire:model="last_name" :label="__('Apellidos')" />
                    </div>
                    <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                        <flux:input wire:model="birth_date" type="date" :label="__('Fecha de nacimiento')" />
                        <flux:input wire:model="phone" :label="__('Celular')" />
                    </div>
                    <flux:input wire:model="address" :label="__('Dirección')" />
                    <flux:input wire:model="email" type="email" :label="__('Correo (usuario de acceso)')" />
                    <flux:text class="text-sm text-brand-text-muted!">
                        {{ __('Se enviará un correo al acudiente para que cree su propia contraseña, igual que al crear un profesor.') }}
                    </flux:text>
                @endif
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mb-5 flex items-start gap-3">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-teal-bg text-teal-deep">
                    <flux:icon icon="academic-cap" variant="micro" class="size-5" />
                </span>
                <div>
                    <flux:heading size="lg">{{ __('Estudiantes a vincular') }}</flux:heading>
                    <flux:text class="text-sm text-brand-text-muted!">{{ __('Puedes seleccionar uno o varios estudiantes de tu institución.') }}</flux:text>
                </div>
            </div>

            <flux:input wire:model.live.debounce.300ms="studentSearch" icon="magnifying-glass" :placeholder="__('Buscar por nombre o número de documento...')" class="mb-4" />

            <div class="flex flex-wrap items-center gap-x-6 gap-y-1">
                @forelse ($this->students as $student)
                    <label wire:key="student-{{ $student->id }}" class="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-1.5 select-none hover:bg-zinc-50 dark:hover:bg-zinc-800/60">
                        <flux:checkbox wire:model="student_ids" value="{{ $student->id }}" />
                        <span class="text-sm text-brand-text" x-on:click="$el.previousElementSibling.click()">{{ $student->user->name }}</span>
                    </label>
                @empty
                    <flux:text class="text-sm text-brand-text-muted!">{{ __('No se encontraron estudiantes disponibles para vincular.') }}</flux:text>
                @endforelse
            </div>
            @error('student_ids')
                <flux:text class="mt-2 text-sm text-red-600! dark:text-red-400!">{{ $message }}</flux:text>
            @enderror
        </div>

        <div class="flex justify-end gap-2">
            <flux:button variant="ghost" href="{{ $backUrl }}" wire:navigate>{{ __('Cancelar') }}</flux:button>
            <flux:button variant="primary" type="submit">{{ __('Crear acudiente') }}</flux:button>
        </div>
    </form>
</section>
