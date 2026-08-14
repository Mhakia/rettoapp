<section class="w-full space-y-8">
    <div>
        <flux:heading size="lg">{{ __('Gestionar retos') }}</flux:heading>

        <form wire:submit="save" class="mt-4 max-w-2xl space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <flux:select wire:model="target_role" :label="__('Dirigido a')">
                    <flux:select.option value="student">{{ __('Estudiante') }}</flux:select.option>
                    <flux:select.option value="teacher">{{ __('Profesor') }}</flux:select.option>
                    <flux:select.option value="guardian">{{ __('Acudiente') }}</flux:select.option>
                </flux:select>

                <flux:select wire:model="status" :label="__('Estado')">
                    <flux:select.option value="draft">{{ __('Borrador') }}</flux:select.option>
                    <flux:select.option value="published">{{ __('Publicado') }}</flux:select.option>
                    <flux:select.option value="archived">{{ __('Archivado') }}</flux:select.option>
                </flux:select>
            </div>

            <flux:input wire:model="title" :label="__('Título')" />
            <flux:textarea wire:model="description" :label="__('Descripción')" />

            <div class="grid grid-cols-3 gap-4">
                <flux:input wire:model="category" :label="__('Categoría')" />
                <flux:input wire:model="points" type="number" :label="__('Puntos')" />
                <flux:select wire:model="difficulty" :label="__('Dificultad')">
                    <flux:select.option value="easy">{{ __('Fácil') }}</flux:select.option>
                    <flux:select.option value="medium">{{ __('Media') }}</flux:select.option>
                    <flux:select.option value="hard">{{ __('Difícil') }}</flux:select.option>
                </flux:select>
            </div>

            <div>
                <flux:label>{{ __('Restringir a instituciones (vacío = todas)') }}</flux:label>
                <div class="mt-2 grid grid-cols-2 gap-2">
                    @foreach ($this->institutions as $institution)
                        <flux:checkbox wire:model="institutionUuids" value="{{ $institution->uuid }}" :label="$institution->name" />
                    @endforeach
                </div>
            </div>

            <flux:button variant="primary" type="submit">{{ $editingId ? __('Actualizar') : __('Crear reto') }}</flux:button>
        </form>
    </div>

    <div>
        <flux:heading size="sm">{{ __('Catálogo') }}</flux:heading>
        <div class="mt-4 divide-y">
            @foreach ($this->challenges as $challenge)
                <div class="flex items-center justify-between py-3">
                    <div>
                        <flux:text class="font-medium">{{ $challenge->title }}</flux:text>
                        <flux:text class="text-sm text-gray-500">
                            {{ $challenge->target_role }} · {{ $challenge->status }} ·
                            {{ $challenge->institutions->isEmpty() ? __('todas las instituciones') : $challenge->institutions->pluck('name')->join(', ') }}
                        </flux:text>
                    </div>
                    <flux:button size="sm" wire:click="edit('{{ $challenge->ulid }}')">{{ __('Editar') }}</flux:button>
                </div>
            @endforeach
        </div>
    </div>
</section>
