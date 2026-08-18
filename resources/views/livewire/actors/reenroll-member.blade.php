<div>
    <flux:button
        icon="arrow-path"
        x-on:click="$dispatch('modal-show', { name: 'reenroll-{{ $role }}' })"
    >
        {{ $role === 'student' ? __('Matricular estudiante existente') : __('Matricular profesor existente') }}
    </flux:button>

    <flux:modal name="reenroll-{{ $role }}" :dismissible="false" class="w-full max-w-lg">
        <div class="space-y-4">
            <div class="flex items-center gap-3">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-teal-bg text-teal-deep">
                    <flux:icon icon="arrow-path" variant="micro" class="size-5" />
                </span>
                <div>
                    <flux:heading size="lg">
                        {{ $role === 'student' ? __('Matricular estudiante existente') : __('Matricular profesor existente') }}
                    </flux:heading>
                    <flux:text class="text-sm text-brand-text-muted!">
                        {{ __('Busca por documento a alguien que ya está registrado pero fue retirado, para volver a matricularlo con una nueva fecha de ingreso.') }}
                    </flux:text>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:select wire:model.live="document_type" :label="__('Tipo de documento')">
                    <flux:select.option value="">{{ __('Selecciona...') }}</flux:select.option>
                    @if ($role === 'student')
                        <flux:select.option value="registro_civil">{{ __('Registro civil') }}</flux:select.option>
                        <flux:select.option value="tarjeta_identidad">{{ __('Tarjeta de identidad') }}</flux:select.option>
                    @else
                        <flux:select.option value="cedula_ciudadania">{{ __('Cédula de ciudadanía') }}</flux:select.option>
                        <flux:select.option value="cedula_extranjeria">{{ __('Cédula de extranjería') }}</flux:select.option>
                        <flux:select.option value="pasaporte">{{ __('Pasaporte') }}</flux:select.option>
                    @endif
                </flux:select>
                <flux:input wire:model.live.debounce.400ms="document_number" :label="__('Número de documento')" />
            </div>

            @if ($found)
                @if ($found['status'] === 'not_found')
                    <div class="flex items-start gap-3 rounded-lg bg-zinc-50 px-4 py-3 text-sm dark:bg-zinc-800/50">
                        <flux:icon icon="information-circle" variant="micro" class="mt-0.5 size-5 shrink-0 text-zinc-400" />
                        <p class="text-brand-text-muted!">
                            {{ __('No se encontró a nadie con ese documento. Si es alguien nuevo, usa ":action".', ['action' => $role === 'student' ? __('Nuevo estudiante') : __('Nuevo profesor')]) }}
                        </p>
                    </div>
                @elseif ($found['status'] === 'active_here')
                    <div class="flex items-start gap-3 rounded-lg bg-amber-bg px-4 py-3 text-sm">
                        <flux:icon icon="exclamation-triangle" variant="micro" class="mt-0.5 size-5 shrink-0 text-amber" />
                        <p class="text-amber">{{ __(':name ya está matriculado activamente en tu institución.', ['name' => $found['name']]) }}</p>
                    </div>
                @elseif ($found['status'] === 'active_elsewhere')
                    <div class="flex items-start gap-3 rounded-lg bg-amber-bg px-4 py-3 text-sm">
                        <flux:icon icon="exclamation-triangle" variant="micro" class="mt-0.5 size-5 shrink-0 text-amber" />
                        <p class="text-amber">{{ __(':name ya está matriculado activamente en :institution.', ['name' => $found['name'], 'institution' => $found['institution_name']]) }}</p>
                    </div>
                @else
                    <form wire:submit="reenroll" class="space-y-4">
                        <div class="flex items-start gap-3 rounded-lg bg-teal-bg px-4 py-3 text-sm">
                            <flux:icon icon="check-circle" variant="micro" class="mt-0.5 size-5 shrink-0 text-teal-deep" />
                            <p class="text-teal-deep">{{ __(':name fue encontrado y está retirado. Elige su grupo para matricularlo de nuevo.', ['name' => $found['name']]) }}</p>
                        </div>

                        @if ($role === 'student')
                            <flux:select wire:model="group_id" :label="__('Grupo')">
                                <flux:select.option value="">{{ __('Selecciona...') }}</flux:select.option>
                                @foreach ($this->groups as $group)
                                    <flux:select.option value="{{ $group->id }}">{{ $group->name }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        @else
                            <div>
                                <flux:text class="mb-2 text-sm font-semibold text-brand-text!">{{ __('Salones o grupos (opcional)') }}</flux:text>
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
                        @endif

                        <div class="flex justify-end gap-2">
                            <flux:modal.close>
                                <flux:button variant="ghost">{{ __('Cancelar') }}</flux:button>
                            </flux:modal.close>
                            <flux:button variant="primary" type="submit">{{ __('Matricular') }}</flux:button>
                        </div>
                    </form>
                @endif
            @endif

            @if (! $found)
                <div class="flex justify-end">
                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('Cerrar') }}</flux:button>
                    </flux:modal.close>
                </div>
            @endif
        </div>
    </flux:modal>
</div>
