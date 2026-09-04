<section class="mx-auto w-full max-w-4xl pb-28">
    <div class="mb-8">
        <flux:button variant="ghost" size="sm" icon="arrow-left" href="{{ route('challenges.manage') }}" wire:navigate class="mb-4">
            {{ __('challenge_back_to_list') }}
        </flux:button>

        <div class="rounded-xl border border-teal-border bg-teal-bg px-6 py-5">
            <flux:heading size="xl" class="text-teal-deep!">{{ $editingId ? __('challenge_edit_title') : __('challenge_create_title') }}</flux:heading>
            <flux:text class="text-brand-text-muted!">
                {{ __('challenge_manage_create_edit_description') }}
            </flux:text>
        </div>
    </div>

    <form class="space-y-6">
        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mb-5 flex items-start gap-3">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-teal-bg text-teal-deep">
                    <flux:icon icon="adjustments-horizontal" variant="micro" class="size-5" />
                </span>
                <div>
                    <flux:heading size="lg">{{ __('challenge_info_section') }}</flux:heading>
                    <flux:text class="text-sm text-brand-text-muted!">{{ __('challenge_info_description') }}</flux:text>
                </div>
            </div>

            <div class="space-y-4">
                    <flux:input wire:model="title" :label="__('field_title')" />
                    <flux:textarea wire:model="description" :label="__('field_description')" rows="3" />

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <flux:select wire:model="target_role" :label="__('challenge_target_role')">
                            <flux:select.option value="student">{{ __('challenge_role_student') }}</flux:select.option>
                            <flux:select.option value="teacher">{{ __('challenge_role_teacher') }}</flux:select.option>
                            <flux:select.option value="guardian">{{ __('challenge_role_guardian') }}</flux:select.option>
                        </flux:select>
                        <flux:input wire:model="category" :label="__('field_category')" />
                        <flux:input wire:model.live="points" type="number" min="1" :label="__('challenge_points_cap')" />
                        <flux:select wire:model="difficulty" :label="__('field_difficulty')">
                            <flux:select.option value="easy">{{ __('challenge_easy') }}</flux:select.option>
                            <flux:select.option value="medium">{{ __('challenge_medium') }}</flux:select.option>
                            <flux:select.option value="hard">{{ __('challenge_hard') }}</flux:select.option>
                </div>

                @if ($this->lockedInstitution)
                    <div class="flex items-center gap-3 rounded-lg border border-amber/30 bg-amber-bg px-4 py-3">
                        <flux:icon icon="lock-closed" variant="micro" class="size-5 shrink-0 text-amber" />
                        <flux:text class="text-brand-text!">
                            {{ __('challenge_exclusive_institution', ['institution' => $this->lockedInstitution->name]) }}
                        </flux:text>
                    </div>
                @else
                    <div>
                        <flux:label>{{ __('challenge_audience_label') }}</flux:label>
                        <flux:radio.group wire:model.live="audienceScope" variant="segmented" class="mt-2">
                            <flux:radio value="all" icon="globe-alt" :label="__('challenge_audience_all')" />
                            <flux:radio value="specific" icon="building-office" :label="__('challenge_audience_specific')" />
                        </flux:radio.group>

                        @if ($audienceScope === 'specific')
                            <div class="mt-3 space-y-3">
                                @if ($this->selectedInstitutions->isNotEmpty())
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($this->selectedInstitutions as $institution)
                                            <span wire:key="selected-institution-{{ $institution->uuid }}" class="inline-flex items-center gap-1.5 rounded-full bg-teal-bg py-1 ps-3 pe-1.5 text-sm font-medium text-teal-deep">
                                                {{ $institution->name }}
                                                <button
                                                    type="button"
                                                    wire:click="removeInstitution('{{ $institution->uuid }}')"
                                                    class="flex size-4 items-center justify-center rounded-full hover:bg-teal-deep/10"
                                                    aria-label="{{ __('challenge_remove_button') }}"
                                                >
                                                    <flux:icon icon="x-mark" variant="micro" class="size-3" />
                                                </button>
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                                <flux:input wire:model.live="institutionSearch" icon="magnifying-glass" :placeholder="__('challenge_institution_search_placeholder')" />

                                <div class="max-h-56 space-y-1 overflow-y-auto rounded-lg border border-zinc-200 p-2 dark:border-zinc-700">
                                    @forelse ($this->filteredInstitutions as $institution)
                                        <label wire:key="institution-{{ $institution->uuid }}" class="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-1.5 select-none hover:bg-zinc-50 dark:hover:bg-zinc-800/60">
                                            <flux:checkbox wire:model="institutionUuids" value="{{ $institution->uuid }}" />
                                            <span class="text-sm text-brand-text" x-on:click="$el.previousElementSibling.click()">{{ $institution->name }}</span>
                                        </label>
                                    @empty
                                        <flux:text class="p-2 text-sm text-brand-text-muted!">{{ __('challenge_no_institutions_found') }}</flux:text>
                                    @endforelse
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
                <div class="flex items-start gap-3">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-teal-bg text-teal-deep">
                        <flux:icon icon="clipboard-document-list" variant="micro" class="size-5" />
                    </span>
                    <div>
                        <flux:heading size="lg">{{ __('challenge_questions_title') }}</flux:heading>
                        <flux:text class="text-sm text-brand-text-muted!">
                            {{ __('challenge_questions_description') }}
                        </flux:text>
                    </div>
                </div>

                <span @class([
                    'whitespace-nowrap rounded-full px-3 py-1 text-xs font-bold uppercase',
                    'bg-red-100 text-red-700' => $this->questionsPointsTotal > $points,
                    'bg-teal-bg text-teal-deep' => $this->questionsPointsTotal <= $points,
                ])>
                    {{ __('challenge_points_display', ['used' => $this->questionsPointsTotal, 'cap' => $points]) }}
                </span>
            </div>

            @error('points')
                <flux:callout variant="danger" icon="x-circle" heading="{{ $message }}" class="mb-4" />
            @enderror
            @error('questions')
                <flux:callout variant="danger" icon="x-circle" heading="{{ $message }}" class="mb-4" />
            @enderror

            <div class="space-y-5">
                @foreach ($questions as $i => $question)
                    <div wire:key="question-{{ $i }}" class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                        <div class="mb-4 flex items-center justify-between">
                            <flux:badge variant="pill">{{ __('challenge_question_number', ['n' => $i + 1]) }}</flux:badge>
                            <flux:button size="sm" variant="danger" icon="trash" wire:click="removeQuestion({{ $i }})" type="button">
                                {{ __('challenge_remove_button') }}
                            </flux:button>
                        </div>

                        <div class="space-y-4">
                            <flux:input wire:model="questions.{{ $i }}.title" :label="__('challenge_question_title_label')" />
                            <flux:textarea wire:model="questions.{{ $i }}.description" :label="__('challenge_question_instructions_label')" rows="2" />

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <flux:input wire:model="questions.{{ $i }}.points" type="number" min="0" :label="__('field_points')" />

                                <flux:radio.group wire:model.live="questions.{{ $i }}.answer_type" :label="__('challenge_answer_type_label')" variant="segmented">
                                    <flux:radio value="choice" :label="__('challenge_answer_type_choice')" />
                                    <flux:radio value="evidence" :label="__('challenge_answer_type_evidence')" />
                                </flux:radio.group>

                                <flux:radio.group wire:model.live="questions.{{ $i }}.scoring_mode" :label="__('challenge_scoring_mode_label')" variant="segmented">
                                    @if ($question['answer_type'] === 'choice')
                                        <flux:radio value="automatic" :label="__('challenge_scoring_automatic')" />
                                    @endif
                                    <flux:radio value="manual" :label="__('challenge_scoring_manual')" />
                                    <flux:radio value="none" :label="__('challenge_scoring_none')" />
                                </flux:radio.group>
                            </div>

                            @if (($question['scoring_mode'] ?? null) === 'manual')
                                <flux:callout variant="info" icon="information-circle" :heading="__('challenge_scoring_manual_heading')" :text="__('challenge_scoring_manual_text', ['points' => $question['points'] ?? 0])" />
                            @endif

                            @if ($question['answer_type'] === 'choice')
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <flux:radio.group wire:model.live="questions.{{ $i }}.answer_mode" :label="__('challenge_answer_mode_label')" variant="segmented">
                                        <flux:radio value="single" :label="__('challenge_answer_mode_single')" />
                                        <flux:radio value="multiple" :label="__('challenge_answer_mode_multiple')" />
                                    </flux:radio.group>

                                    @if (($question['answer_mode'] ?? null) === 'multiple')
                                        <flux:input wire:model="questions.{{ $i }}.min_selections" type="number" min="1" max="3" :label="__('challenge_min_selections_label')" />
                                    @endif

                                    @if (($question['scoring_mode'] ?? null) !== 'manual')
                                        <flux:checkbox wire:model="questions.{{ $i }}.auto_verify" :label="__('challenge_auto_verify_label')" />
                                    @endif
                                </div>

                                <div class="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-800/50">
                                    <div class="mb-3 flex items-center justify-between">
                                        <flux:label>{{ __('challenge_options_title') }}</flux:label>
                                        <flux:button size="sm" icon="plus" wire:click="addOption({{ $i }})" type="button">
                                            {{ __('challenge_add_option_button') }}
                                        </flux:button>
                                    </div>

                                    <div class="space-y-2">
                                        @foreach ($question['options'] as $j => $option)
                                            <div wire:key="question-{{ $i }}-option-{{ $j }}" class="flex items-center gap-2">
                                                @if (($question['scoring_mode'] ?? null) === 'automatic')
                                                    @if (($question['answer_mode'] ?? null) === 'multiple')
                                                        <flux:checkbox wire:model="questions.{{ $i }}.options.{{ $j }}.is_correct" />
                                                    @else
                                                        <button
                                                            type="button"
                                                            wire:click="selectSingleCorrectOption({{ $i }}, {{ $j }})"
                                                            class="flex size-5 shrink-0 items-center justify-center rounded-full border-2 {{ ($option['is_correct'] ?? false) ? 'border-teal-deep bg-teal-deep' : 'border-zinc-300 dark:border-zinc-600' }}"
                                                            aria-label="{{ __('challenge_mark_correct_aria') }}"
                                                        >
                                                            @if ($option['is_correct'] ?? false)
                                                                <span class="size-2 rounded-full bg-white"></span>
                                                            @endif
                                                        </button>
                                                    @endif
                                                @endif

                                                <flux:input wire:model="questions.{{ $i }}.options.{{ $j }}.label" :placeholder="__('challenge_option_text_placeholder')" class="grow" />

                                                <flux:button size="sm" variant="ghost" icon="x-mark" wire:click="removeOption({{ $i }}, {{ $j }})" type="button" />
                                            </div>
                                        @endforeach
                                    </div>

                                    @error("questions.{$i}.options")
                                        <flux:text class="mt-2 text-sm font-medium text-red-500 dark:text-red-400">{{ $message }}</flux:text>
                                    @enderror
                                    @error("questions.{$i}.answer_mode")
                                        <flux:text class="mt-2 text-sm font-medium text-red-500 dark:text-red-400">{{ $message }}</flux:text>
                                    @enderror
                                    @error("questions.{$i}.min_selections")
                                        <flux:text class="mt-2 text-sm font-medium text-red-500 dark:text-red-400">{{ $message }}</flux:text>
                                    @enderror

                                    <flux:text class="mt-2 text-xs text-brand-text-muted!">
                                        @if (($question['scoring_mode'] ?? null) === 'automatic')
                                            {{ __('challenge_answer_single_hint') }}
                                        @elseif (($question['scoring_mode'] ?? null) === 'manual')
                                            {{ __('challenge_answer_manual_hint') }}
                                        @else
                                            {{ __('challenge_answer_none_hint') }}
                                        @endif
                                    </flux:text>
                                </div>
                            @else
                                <flux:text class="text-sm text-brand-text-muted!">
                                    @if (($question['scoring_mode'] ?? null) === 'manual')
                                        {{ __('challenge_evidence_manual_text', ['points' => $question['points'] ?? 0]) }}
                                    @else
                                        {{ __('challenge_evidence_none_text') }}
                                    @endif
                                </flux:text>
                            @endif
                        </div>
                    </div>
                @endforeach

                @if (empty($questions))
                    <div class="rounded-lg border border-dashed border-zinc-300 p-6 text-center dark:border-zinc-700">
                        <flux:text class="text-brand-text-muted!">
                            {{ __('challenge_no_questions_added') }}
                        </flux:text>
                    </div>
                @endif
            </div>

            <flux:button class="mt-5" icon="plus" wire:click="addQuestion" type="button">
                {{ __('challenge_add_question_button') }}
            </flux:button>
        </div>

        <div class="sticky bottom-4 z-10 flex justify-end gap-2 rounded-xl border border-zinc-200 bg-white/95 p-4 shadow-lg backdrop-blur dark:border-zinc-700 dark:bg-zinc-900/95">
            <flux:button wire:click="cancel" type="button">{{ __('action_cancel') }}</flux:button>
            <flux:button wire:click="save" type="button" icon="document-check">
                {{ __('action_save') }}
            </flux:button>
            <flux:button variant="primary" wire:click="publish" type="button" icon="megaphone" class="bg-teal! hover:bg-teal-deep!">
                {{ __('challenge_action_publish') }}
            </flux:button>
        </div>
    </form>
</section>
