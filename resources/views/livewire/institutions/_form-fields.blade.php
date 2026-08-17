                <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                    <flux:input wire:model="name" :label="__('Nombre')" />
                    <flux:input wire:model="nit" :label="__('NIT')" />
                </div>
                <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                    <flux:input wire:model="address" :label="__('Dirección')" />
                    <flux:input wire:model="phone" :label="__('Teléfono')" />
                </div>
                <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-3">
                    <flux:input wire:model="country" :label="__('País')" />
                    <flux:input wire:model="state" :label="__('Departamento o estado')" />
                    <flux:input wire:model="city" :label="__('Ciudad')" />
                </div>

                <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                    <flux:select wire:model="bulletin_frequency" :label="__('Frecuencia de boletines')">
                        <flux:select.option value="weekly">{{ __('Semanal') }}</flux:select.option>
                        <flux:select.option value="biweekly">{{ __('Quincenal') }}</flux:select.option>
                        <flux:select.option value="monthly">{{ __('Mensual') }}</flux:select.option>
                        <flux:select.option value="disabled">{{ __('Deshabilitado') }}</flux:select.option>
                    </flux:select>

                    <flux:select wire:model="entity_type" :label="__('Tipo de entidad educativa')">
                        <flux:select.option value="">{{ __('Selecciona...') }}</flux:select.option>
                        <flux:select.option value="public">{{ __('Pública') }}</flux:select.option>
                        <flux:select.option value="private">{{ __('Privada') }}</flux:select.option>
                    </flux:select>
                </div>

                <flux:heading size="sm" class="text-teal-deep!">{{ __('Contacto de la institución') }}</flux:heading>
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

                <flux:heading size="sm" class="text-teal-deep!">{{ __('Rector o director') }}</flux:heading>
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
