<section class="mx-auto w-full max-w-4xl pb-28">
    <div class="mb-8">
        <flux:button variant="ghost" size="sm" icon="arrow-left" href="{{ route('institutions.index') }}" wire:navigate class="mb-4">
            {{ __('institution_back_to_list') }}
        </flux:button>

        <div class="rounded-xl border border-teal-border bg-teal-bg px-6 py-5">
            <flux:heading size="xl" class="text-teal-deep!">{{ __('institution_create_page_title') }}</flux:heading>
            <flux:text class="text-brand-text-muted!">
                {{ __('institution_create_description') }}
            </flux:text>
        </div>
    </div>

    <form wire:submit="store" class="space-y-6">
        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mb-5 flex items-start gap-3">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-teal-bg text-teal-deep">
                    <flux:icon icon="building-office-2" variant="micro" class="size-5" />
                </span>
                <div>
                    <flux:heading size="lg">{{ __('institution_general_data_section') }}</flux:heading>
                    <flux:text class="text-sm text-brand-text-muted!">{{ __('institution_general_data_description') }}</flux:text>
                </div>
            </div>

            <div class="space-y-4">
                <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                    <flux:input wire:model="name" :label="__('Nombre de la institución')" />
                    <flux:input wire:model="nit" :label="__('field_nit')" />
                </div>
                <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                    <flux:input wire:model="address" :label="__('field_address')" />
                    <flux:input wire:model="phone" :label="__('field_phone')" />
                </div>
                <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-3">
                    <flux:input wire:model="country" :label="__('field_country')" />
                    <flux:input wire:model="state" :label="__('field_state')" />
                    <flux:input wire:model="city" :label="__('field_city')" />
                </div>
                <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                    <flux:select wire:model="entity_type" :label="__('field_entity_type')">
                        <flux:select.option value="">{{ __('institution_select_placeholder') }}</flux:select.option>
                        <flux:select.option value="public">{{ __('institution_entity_public') }}</flux:select.option>
                        <flux:select.option value="private">{{ __('institution_entity_private') }}</flux:select.option>
                    </flux:select>
                    <flux:select wire:model="bulletin_frequency" :label="__('Frecuencia de boletines')">
                        <flux:select.option value="weekly">{{ __('Semanal') }}</flux:select.option>
                        <flux:select.option value="biweekly">{{ __('Quincenal') }}</flux:select.option>
                        <flux:select.option value="monthly">{{ __('Mensual') }}</flux:select.option>
                        <flux:select.option value="disabled">{{ __('Deshabilitado') }}</flux:select.option>
                    </flux:select>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mb-5 flex items-start gap-3">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-teal-bg text-teal-deep">
                    <flux:icon icon="identification" variant="micro" class="size-5" />
                </span>
                <div>
                    <flux:heading size="lg">{{ __('Contacto de la institución') }}</flux:heading>
                    <flux:text class="text-sm text-brand-text-muted!">{{ __('Persona a quien podemos escribirle o llamar por temas operativos.') }}</flux:text>
                </div>
            </div>

            <div class="space-y-4">
                <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                    <flux:input wire:model="contact_first_name" :label="__('Nombre')" />
                    <flux:input wire:model="contact_middle_name" :label="__('Segundo nombre (opcional)')" />
                </div>
                <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                    <flux:input wire:model="contact_last_name" :label="__('Apellido')" />
                    <flux:input wire:model="contact_second_last_name" :label="__('Segundo apellido')" />
                </div>
                <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                    <flux:select wire:model="contact_document_type" :label="__('Tipo de documento')">
                        <flux:select.option value="">{{ __('Selecciona...') }}</flux:select.option>
                        <flux:select.option value="cedula_ciudadania">{{ __('Cédula de ciudadanía') }}</flux:select.option>
                        <flux:select.option value="cedula_extranjeria">{{ __('Cédula de extranjería') }}</flux:select.option>
                        <flux:select.option value="pasaporte">{{ __('Pasaporte') }}</flux:select.option>
                    </flux:select>
                    <flux:input wire:model="contact_document_number" :label="__('Número de documento')" />
                </div>
                <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                    <flux:input wire:model="contact_email" type="email" :label="__('Correo de contacto')" />
                    <flux:input wire:model="contact_phone" :label="__('Celular de contacto')" />
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mb-5 flex items-start gap-3">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-teal-bg text-teal-deep">
                    <flux:icon icon="academic-cap" variant="micro" class="size-5" />
                </span>
                <div>
                    <flux:heading size="lg">{{ __('Rector o director') }}</flux:heading>
                    <flux:text class="text-sm text-brand-text-muted!">{{ __('Máxima autoridad de la institución.') }}</flux:text>
                </div>
            </div>

            <div class="space-y-4">
                <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                    <flux:input wire:model="principal_name" :label="__('Nombre completo')" />
                    <flux:input wire:model="principal_started_at" type="date" :label="__('Fecha de ingreso (opcional)')" />
                </div>
                <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                    <flux:select wire:model="principal_document_type" :label="__('Tipo de documento')">
                        <flux:select.option value="">{{ __('Selecciona...') }}</flux:select.option>
                        <flux:select.option value="cedula_ciudadania">{{ __('Cédula de ciudadanía') }}</flux:select.option>
                        <flux:select.option value="cedula_extranjeria">{{ __('Cédula de extranjería') }}</flux:select.option>
                        <flux:select.option value="pasaporte">{{ __('Pasaporte') }}</flux:select.option>
                    </flux:select>
                    <flux:input wire:model="principal_document_number" :label="__('Número de documento')" />
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-amber/30 bg-amber-bg p-6">
            <div class="mb-5 flex items-start gap-3">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-white text-amber dark:bg-zinc-900">
                    <flux:icon icon="shield-check" variant="micro" class="size-5" />
                </span>
                <div>
                    <flux:heading size="lg" class="text-brand-text!">{{ __('Administrador de la institución') }}</flux:heading>
                    <flux:text class="text-sm text-brand-text-muted!">
                        {{ __('Este será el único usuario con acceso completo a los datos de la institución. Le enviaremos un correo para que active su cuenta y elija su propia contraseña.') }}
                    </flux:text>
                </div>
            </div>

            <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                <flux:input wire:model="admin_name" :label="__('Nombre')" />
                <flux:input wire:model="admin_email" type="email" :label="__('Correo')" />
            </div>
        </div>

        <div class="sticky bottom-4 z-10 flex justify-end gap-2 rounded-xl border border-zinc-200 bg-white/95 p-4 shadow-lg backdrop-blur dark:border-zinc-700 dark:bg-zinc-900/95">
            <flux:button href="{{ route('institutions.index') }}" wire:navigate>{{ __('Cancelar') }}</flux:button>
            <flux:button variant="primary" type="submit" icon="check-circle" class="bg-teal! hover:bg-teal-deep!">
                {{ __('Crear institución') }}
            </flux:button>
        </div>
    </form>
</section>
