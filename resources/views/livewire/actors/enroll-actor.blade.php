<section class="w-full">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="lg">{{ __('Matricular / vincular') }}</flux:heading>
            <flux:text>{{ __('Registra un student, teacher o guardian nuevo, o vincula uno existente en la plataforma.') }}</flux:text>
        </div>
        <flux:button href="{{ route('actors.roster') }}" wire:navigate>{{ __('Ver matrículas activas') }}</flux:button>
    </div>

    <form wire:submit="enroll" class="max-w-xl space-y-6">
        <flux:radio.group wire:model.live="role" :label="__('Rol')" variant="segmented">
            <flux:radio value="student" :label="__('Estudiante')" />
            <flux:radio value="teacher" :label="__('Profesor')" />
            <flux:radio value="guardian" :label="__('Acudiente')" />
        </flux:radio.group>

        <div class="grid grid-cols-2 gap-4">
            <flux:select wire:model="document_type" :label="__('Tipo de documento')">
                <flux:select.option value="">{{ __('Selecciona...') }}</flux:select.option>
                <flux:select.option value="registro_civil">{{ __('Registro civil') }}</flux:select.option>
                <flux:select.option value="tarjeta_identidad">{{ __('Tarjeta de identidad') }}</flux:select.option>
                <flux:select.option value="cedula_ciudadania">{{ __('Cédula de ciudadanía') }}</flux:select.option>
                <flux:select.option value="cedula_extranjeria">{{ __('Cédula de extranjería') }}</flux:select.option>
            </flux:select>

            <flux:input wire:model="document_number" :label="__('Número de documento')" />
        </div>

        <flux:input wire:model="name" :label="__('Nombre completo')" />
        <flux:input wire:model="email" type="email" :label="__('Correo (opcional)')" />

        @if ($role !== 'guardian')
            <flux:select wire:model="group_id" :label="__('Grupo')">
                <flux:select.option value="">{{ __('Selecciona...') }}</flux:select.option>
                @foreach ($this->groups as $group)
                    <flux:select.option value="{{ $group->id }}">{{ $group->name }}</flux:select.option>
                @endforeach
            </flux:select>
        @else
            <flux:select wire:model="existing_student_uuid" :label="__('Estudiante a vincular')">
                <flux:select.option value="">{{ __('Selecciona...') }}</flux:select.option>
                @foreach ($this->students as $student)
                    <flux:select.option value="{{ $student->uuid }}">{{ $student->user->name }}</flux:select.option>
                @endforeach
            </flux:select>
        @endif

        <flux:button variant="primary" type="submit">{{ __('Guardar') }}</flux:button>
    </form>
</section>
