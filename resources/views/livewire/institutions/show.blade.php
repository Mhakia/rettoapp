<section class="w-full">
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4 rounded-xl border border-teal-border bg-teal-bg px-6 py-5">
        <div>
            <flux:heading size="xl" class="text-teal-deep!">{{ $institution->name }}</flux:heading>
            <flux:text class="text-brand-text-muted!">{{ $institution->nit ?? __('institution_no_nit') }} · {{ $institution->address ?? __('institution_no_address') }}</flux:text>
        </div>
        <flux:button icon="arrow-left" href="{{ route('institutions.index') }}" wire:navigate>{{ __('institution_back_to_list') }}</flux:button>
    </div>

    <flux:text class="mb-4 text-sm text-brand-text-muted!">
        {{ __('institution_choose_section', ['name' => $institution->name]) }}
    </flux:text>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <a href="{{ route('directory.students', ['institution' => $institution->uuid]) }}" wire:navigate class="group rounded-xl border border-zinc-200 bg-white p-6 shadow-sm transition hover:border-teal-border hover:shadow-md dark:border-zinc-700 dark:bg-zinc-900">
            <span class="flex size-11 items-center justify-center rounded-lg bg-teal-bg text-teal-deep">
                <flux:icon icon="academic-cap" variant="micro" class="size-6" />
            </span>
            <flux:heading size="lg" class="mt-4 group-hover:text-teal-deep!">{{ __('institution_directory_students') }}</flux:heading>
            <flux:text class="text-sm text-brand-text-muted!">{{ __('institution_directory_students_description') }}</flux:text>
        </a>

        <a href="{{ route('directory.teachers', ['institution' => $institution->uuid]) }}" wire:navigate class="group rounded-xl border border-zinc-200 bg-white p-6 shadow-sm transition hover:border-teal-border hover:shadow-md dark:border-zinc-700 dark:bg-zinc-900">
            <span class="flex size-11 items-center justify-center rounded-lg bg-teal-bg text-teal-deep">
                <flux:icon icon="user-group" variant="micro" class="size-6" />
            </span>
            <flux:heading size="lg" class="mt-4 group-hover:text-teal-deep!">{{ __('institution_directory_teachers') }}</flux:heading>
            <flux:text class="text-sm text-brand-text-muted!">{{ __('institution_directory_teachers_description') }}</flux:text>
        </a>

        <a href="{{ route('directory.guardians', ['institution' => $institution->uuid]) }}" wire:navigate class="group rounded-xl border border-zinc-200 bg-white p-6 shadow-sm transition hover:border-teal-border hover:shadow-md dark:border-zinc-700 dark:bg-zinc-900">
            <span class="flex size-11 items-center justify-center rounded-lg bg-teal-bg text-teal-deep">
                <flux:icon icon="heart" variant="micro" class="size-6" />
            </span>
            <flux:heading size="lg" class="mt-4 group-hover:text-teal-deep!">{{ __('institution_directory_guardians') }}</flux:heading>
            <flux:text class="text-sm text-brand-text-muted!">{{ __('institution_directory_guardians_description') }}</flux:text>
        </a>
    </div>
</section>
