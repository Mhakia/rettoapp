<section class="w-full">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="lg">{{ __('Estudiantes y profesores') }}</flux:heading>
            <flux:text>{{ __('Matrículas activas en tu institución.') }}</flux:text>
        </div>
        <flux:button href="{{ route('actors.enroll') }}" wire:navigate>{{ __('Matricular / vincular') }}</flux:button>
    </div>

    <flux:radio.group wire:model.live="role" variant="segmented" class="mb-6">
        <flux:radio value="student" :label="__('Estudiantes')" />
        <flux:radio value="teacher" :label="__('Profesores')" />
    </flux:radio.group>

    <div class="divide-y">
        @forelse ($this->memberships as $membership)
            <div class="py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:text class="font-medium">{{ $membership->user->name }}</flux:text>
                        <flux:text class="text-sm text-gray-500">
                            {{ $membership->group?->name ?? __('Sin grupo') }} ·
                            {{ __('Desde :date', ['date' => $membership->started_at->format('d/m/Y')]) }}
                        </flux:text>
                    </div>
                </div>
                <livewire:actors.withdraw-membership :membership="$membership" :key="$membership->id" />
            </div>
        @empty
            <flux:text class="py-4">{{ __('No hay matrículas activas para este rol.') }}</flux:text>
        @endforelse
    </div>
</section>
