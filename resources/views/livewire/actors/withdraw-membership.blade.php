<div>
    <form wire:submit="withdraw" class="flex items-end gap-4">
        <flux:input wire:model="reason" :label="__('Motivo del retiro')" class="grow" />
        <flux:button variant="danger" type="submit">{{ __('Retirar') }}</flux:button>
    </form>
</div>
