<div>
    @if (session('guardian_return_id'))
        <div class="fixed inset-x-0 top-0 z-50 flex items-center justify-center gap-4 bg-teal-deep px-4 py-2 text-sm text-white shadow-md">
            <flux:icon icon="eye" variant="micro" class="size-4" />
            <span>{{ __('Estás viendo los retos de :name.', ['name' => auth()->user()->name]) }}</span>
            <flux:button size="sm" variant="ghost" class="text-white! hover:bg-white/10!" wire:click="returnToGuardian">
                {{ __('Volver a mi cuenta') }}
            </flux:button>
        </div>
    @endif
</div>

