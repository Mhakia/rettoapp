<div class="flex flex-col items-center gap-8 text-center">
    <div class="flex flex-col items-center gap-2">
        <flux:heading size="xl">{{ __('Ingresar a mis retos') }}</flux:heading>
        <flux:text class="text-brand-text-muted!">
            @if ($groupName)
                {{ __('group_tap_to_enter', ['group' => $groupName]) }}
            @else
                {{ __('enter_teacher_code') }}
            @endif
        </flux:text>
    </div>

    @if (! $groupName)
        <form wire:submit="submitCode" class="flex w-full max-w-xs flex-col items-center gap-4">
            <flux:input
                wire:model="code"
                :placeholder="__('class_session_code_placeholder')"
                autofocus
                autocomplete="off"
                maxlength="10"
                class="h-16! w-full rounded-2xl! text-center text-2xl tracking-widest uppercase"
            />

            <flux:button
                type="submit"
                variant="primary"
                class="h-14 w-full rounded-2xl bg-teal! text-base! font-semibold hover:bg-teal-deep!"
            >
                {{ __('enter_button') }}
            </flux:button>
        </form>
    @else
        <div class="flex w-full flex-col gap-3">
            @php
                $palette = ['bg-rose-500', 'bg-amber-500', 'bg-emerald-500', 'bg-sky-500', 'bg-violet-500', 'bg-orange-500'];
            @endphp

            @forelse ($this->students as $index => $student)
                <button
                    type="button"
                    wire:key="join-student-{{ $student['uuid'] }}"
                    wire:click="selectStudent('{{ $student['uuid'] }}')"
                    x-data
                    x-init="setTimeout(() => $el.classList.remove('opacity-0', 'translate-y-2'), {{ 60 + $index * 80 }})"
                    class="flex translate-y-2 items-center gap-4 rounded-3xl border border-zinc-700 bg-zinc-900 p-5 opacity-0 shadow-sm transition-all duration-500 ease-out active:scale-95 hover:border-teal hover:bg-zinc-800"
                >
                    <span class="flex size-16 shrink-0 items-center justify-center rounded-full {{ $palette[$index % count($palette)] }} text-base font-bold text-white shadow-md">
                        {{ $student['initials'] }}
                    </span>
                    <span class="text-left text-lg font-semibold">{{ $student['name'] }}</span>
                </button>
            @empty
                <flux:text class="block py-6 text-center text-brand-text-muted!">
                    {{ __('no_active_students_group') }}
                </flux:text>
            @endforelse
        </div>

        <flux:button wire:click="backToCode" variant="ghost" icon="arrow-left" class="text-base!">
            {{ __('Usar otro código') }}
        </flux:button>
    @endif
</div>
