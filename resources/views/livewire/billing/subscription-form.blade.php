<section class="mx-auto w-full max-w-3xl pb-16">
    <div class="mb-8">
        <flux:button variant="ghost" size="sm" icon="arrow-left" href="{{ route('billing.subscriptions.index') }}" wire:navigate class="mb-4">
            {{ __('Volver') }}
        </flux:button>

        <div class="rounded-xl border border-teal-border bg-teal-bg px-6 py-5">
            <flux:heading size="xl" class="text-teal-deep!">{{ $subscription ? __('billing_subscription_edit_title') : __('billing_subscription_create_title') }}</flux:heading>
            <flux:text class="text-brand-text-muted!">{{ __('billing_subscription_form_description') }}</flux:text>
        </div>
    </div>

    <form wire:submit="save" class="space-y-6">
        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mb-5 flex items-start gap-3">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-teal-bg text-teal-deep">
                    <flux:icon icon="building-office-2" variant="micro" class="size-5" />
                </span>
                <div>
                    <flux:heading size="lg">{{ __('billing_section_institution') }}</flux:heading>
                    <flux:text class="text-sm text-brand-text-muted!">{{ __('billing_section_institution_description') }}</flux:text>
                </div>
            </div>

            @if ($institutionId)
                <div class="flex items-center justify-between gap-3 rounded-lg border border-teal-border bg-teal-bg px-4 py-3">
                    <div class="flex items-center gap-3">
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-white text-teal-deep dark:bg-zinc-900">
                            <flux:icon icon="building-office-2" variant="micro" class="size-5" />
                        </span>
                        <flux:text class="font-semibold text-teal-deep!">{{ $institutionName }}</flux:text>
                    </div>
                    <flux:button size="sm" variant="ghost" icon="x-mark" :tooltip="__('billing_institution_change')" wire:click="clearInstitution" />
                </div>
            @else
                <flux:input wire:model.live.debounce.300ms="institutionSearch" icon="magnifying-glass" :placeholder="__('billing_institution_search_placeholder')" />

                @if (trim($institutionSearch) !== '')
                    <div class="mt-2 max-h-72 divide-y divide-zinc-100 overflow-y-auto rounded-lg border border-zinc-200 dark:divide-zinc-800 dark:border-zinc-700">
                        @forelse ($this->institutionResults as $result)
                            <button type="button" wire:key="institution-result-{{ $result->id }}" wire:click="selectInstitution({{ $result->id }})" class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left transition hover:bg-teal-bg">
                                <span class="font-medium text-brand-text">{{ $result->name }}</span>
                                <span class="text-xs text-brand-text-muted!">{{ $result->nit }}</span>
                            </button>
                        @empty
                            <div class="px-4 py-6 text-center text-sm text-brand-text-muted!">{{ __('billing_institution_no_results') }}</div>
                        @endforelse
                    </div>
                @endif
            @endif

            <flux:error name="institutionId" class="mt-2" />
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mb-5 flex items-start gap-3">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-teal-bg text-teal-deep">
                    <flux:icon icon="tag" variant="micro" class="size-5" />
                </span>
                <div>
                    <flux:heading size="lg">{{ __('billing_section_plan') }}</flux:heading>
                    <flux:text class="text-sm text-brand-text-muted!">{{ __('billing_section_plan_description') }}</flux:text>
                </div>
            </div>

            <flux:select wire:model.live="planId" :label="__('billing_plan_optional')">
                <flux:select.option value="">{{ __('option_none') }}</flux:select.option>
                @foreach ($this->plans as $plan)
                    <flux:select.option value="{{ $plan->id }}">{{ $plan->name }} ({{ number_format($plan->base_price, 2) }})</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mb-5 flex items-start gap-3">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-teal-bg text-teal-deep">
                    <flux:icon icon="banknotes" variant="micro" class="size-5" />
                </span>
                <div>
                    <flux:heading size="lg">{{ __('billing_section_pricing') }}</flux:heading>
                    <flux:text class="text-sm text-brand-text-muted!">{{ __('billing_section_pricing_description') }}</flux:text>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:input wire:model.number="basePrice" type="number" step="0.01" min="0" :label="__('billing_plan_base_price')" required />
                <flux:input wire:model.number="includedStudents" type="number" min="0" :label="__('billing_plan_included_students')" required />
                <flux:input wire:model.number="pricePerExtraStudent" type="number" step="0.01" min="0" :label="__('billing_plan_extra_student_price')" required />
                <flux:select wire:model="billingCycle" :label="__('billing_plan_billing_cycle')">
                    <flux:select.option value="monthly">{{ __('frequency_monthly') }}</flux:select.option>
                    <flux:select.option value="quarterly">{{ __('frequency_quarterly') }}</flux:select.option>
                    <flux:select.option value="yearly">{{ __('frequency_yearly') }}</flux:select.option>
                </flux:select>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mb-5 flex items-start gap-3">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-teal-bg text-teal-deep">
                    <flux:icon icon="adjustments-horizontal" variant="micro" class="size-5" />
                </span>
                <div>
                    <flux:heading size="lg">{{ __('billing_section_status') }}</flux:heading>
                    <flux:text class="text-sm text-brand-text-muted!">{{ __('billing_section_status_description') }}</flux:text>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <flux:select wire:model="status" :label="__('field_status')">
                    <flux:select.option value="active">{{ __('subscription_status_active') }}</flux:select.option>
                    <flux:select.option value="inactive">{{ __('subscription_status_inactive') }}</flux:select.option>
                    <flux:select.option value="paused">{{ __('subscription_status_paused') }}</flux:select.option>
                </flux:select>
                <flux:select wire:model.live="discountType" :label="__('billing_discount_type')">
                    <flux:select.option value="none">{{ __('option_none') }}</flux:select.option>
                    <flux:select.option value="fixed">{{ __('billing_discount_fixed') }}</flux:select.option>
                    <flux:select.option value="percentage">{{ __('billing_discount_percentage') }}</flux:select.option>
                </flux:select>
                @if ($discountType !== 'none')
                    <flux:input wire:model.number="discountValue" type="number" step="0.01" min="0" max="{{ $discountType === 'percentage' ? 100 : null }}" :label="__('billing_discount_value')" required />
                @endif
            </div>
        </div>

        <div class="flex gap-3">
            <flux:button type="submit" variant="primary" class="bg-teal! hover:bg-teal-deep!">{{ __('action_save') }}</flux:button>
            <flux:button href="{{ route('billing.subscriptions.index') }}" wire:navigate variant="subtle">{{ __('action_cancel') }}</flux:button>
        </div>
    </form>
</section>

