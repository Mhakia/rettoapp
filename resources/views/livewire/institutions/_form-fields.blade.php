                <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                    <flux:input wire:model="name" :label="__('field_name')" />
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
                    <flux:select wire:model="bulletin_frequency" :label="__('institution_bulletin_frequency')">
                        <flux:select.option value="weekly">{{ __('frequency_weekly') }}</flux:select.option>
                        <flux:select.option value="biweekly">{{ __('frequency_biweekly') }}</flux:select.option>
                        <flux:select.option value="monthly">{{ __('frequency_monthly') }}</flux:select.option>
                        <flux:select.option value="disabled">{{ __('frequency_disabled') }}</flux:select.option>
                    </flux:select>

                    <flux:select wire:model="entity_type" :label="__('field_entity_type')">
                        <flux:select.option value="">{{ __('option_select') }}</flux:select.option>
                        <flux:select.option value="public">{{ __('institution_entity_public') }}</flux:select.option>
                        <flux:select.option value="private">{{ __('institution_entity_private') }}</flux:select.option>
                    </flux:select>
                </div>

                <flux:heading size="sm" class="text-teal-deep!">{{ __('institution_contact_section') }}</flux:heading>
                <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                    <flux:input wire:model="contact_first_name" :label="__('field_name')" />
                    <flux:input wire:model="contact_middle_name" :label="__('field_middle_name_optional')" />
                </div>
                <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                    <flux:input wire:model="contact_last_name" :label="__('field_last_name')" />
                    <flux:input wire:model="contact_second_last_name" :label="__('field_second_last_name')" />
                </div>
                <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                    <flux:select wire:model="contact_document_type" :label="__('field_document_type')">
                        <flux:select.option value="">{{ __('option_select') }}</flux:select.option>
                        <flux:select.option value="cedula_ciudadania">{{ __('document_type_cedula') }}</flux:select.option>
                        <flux:select.option value="cedula_extranjeria">{{ __('document_type_cedula_foreign') }}</flux:select.option>
                        <flux:select.option value="pasaporte">{{ __('document_type_passport') }}</flux:select.option>
                    </flux:select>
                    <flux:input wire:model="contact_document_number" :label="__('field_document_number')" />
                </div>
                <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                    <flux:input wire:model="contact_email" type="email" :label="__('field_contact_email')" />
                    <flux:input wire:model="contact_phone" :label="__('field_contact_phone')" />
                </div>

                <flux:heading size="sm" class="text-teal-deep!">{{ __('institution_principal_section') }}</flux:heading>
                <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                    <flux:input wire:model="principal_name" :label="__('field_full_name')" />
                    <flux:input wire:model="principal_started_at" type="date" :label="__('field_started_date_optional')" />
                </div>
                <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                    <flux:select wire:model="principal_document_type" :label="__('field_document_type')">
                        <flux:select.option value="">{{ __('option_select') }}</flux:select.option>
                        <flux:select.option value="cedula_ciudadania">{{ __('document_type_cedula') }}</flux:select.option>
                        <flux:select.option value="cedula_extranjeria">{{ __('document_type_cedula_foreign') }}</flux:select.option>
                        <flux:select.option value="pasaporte">{{ __('document_type_passport') }}</flux:select.option>
                    </flux:select>
                    <flux:input wire:model="principal_document_number" :label="__('field_document_number')" />
                </div>
