<div>
    <flux:button
        size="sm"
        variant="ghost"
        icon="user-minus"
        :tooltip="__('Retirar matrícula')"
        class="text-red-500! hover:bg-red-50! dark:hover:bg-red-950/40!"
        x-on:click="$dispatch('modal-show', { name: 'withdraw-{{ $membershipId }}' })"
    />

    <flux:modal name="withdraw-{{ $membershipId }}" :dismissible="false" class="w-full max-w-md">
        <div class="space-y-4">
            <div class="flex items-center gap-3">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-400">
                    <flux:icon icon="exclamation-triangle" variant="micro" class="size-5" />
                </span>
                <div>
                    <flux:heading size="lg">{{ __('Retirar matrícula') }}</flux:heading>
                    <flux:text class="text-sm text-brand-text-muted!">{{ __('Esta acción cerrará la matrícula activa. Puedes indicar un motivo opcional.') }}</flux:text>
                </div>
            </div>

            <form wire:submit="withdraw" class="space-y-4">
                <flux:textarea wire:model="reason" :label="__('Motivo del retiro (opcional)')" rows="3" />
                <div class="flex justify-end gap-2">
                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('Cancelar') }}</flux:button>
                    </flux:modal.close>
                    <flux:button variant="danger" type="submit">{{ __('Retirar') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
