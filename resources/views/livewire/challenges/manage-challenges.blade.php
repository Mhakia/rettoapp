<section class="mx-auto w-full max-w-4xl pb-28">
    <div class="mb-8">
        <flux:button variant="ghost" size="sm" icon="arrow-left" href="{{ route('challenges.manage') }}" wire:navigate class="mb-4">
            {{ __('Volver a retos') }}
        </flux:button>

        <div class="rounded-xl border border-teal-border bg-teal-bg px-6 py-5">
            <flux:heading size="xl" class="text-teal-deep!">{{ $editingId ? __('Editar reto') : __('Crear reto') }}</flux:heading>
            <flux:text class="text-brand-text-muted!">
                {{ __('Define la información del reto y sus preguntas: de opción única, de opción múltiple o de evidencia.') }}
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
                    <flux:heading size="lg">{{ __('Información general') }}</flux:heading>
                    <flux:text class="text-sm text-brand-text-muted!">{{ __('Datos básicos del reto y a quién va dirigido.') }}</flux:text>
                </div>
            </div>

            <div class="space-y-4">
                <flux:input wire:model="title" :label="__('Título')" />
                <flux:textarea wire:model="description" :label="__('Descripción')" rows="3" />

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <flux:select wire:model="target_role" :label="__('Dirigido a')">
                        <flux:select.option value="student">{{ __('Estudiante') }}</flux:select.option>
                        <flux:select.option value="teacher">{{ __('Profesor') }}</flux:select.option>
                        <flux:select.option value="guardian">{{ __('Acudiente') }}</flux:select.option>
                    </flux:select>
                    <flux:input wire:model="category" :label="__('Categoría')" />
                    <flux:input wire:model.live="points" type="number" min="1" :label="__('Tope de puntos del reto')" />
                    <flux:select wire:model="difficulty" :label="__('Dificultad')">
                        <flux:select.option value="easy">{{ __('Fácil') }}</flux:select.option>
                        <flux:select.option value="medium">{{ __('Media') }}</flux:select.option>
                        <flux:select.option value="hard">{{ __('Difícil') }}</flux:select.option>
                    </flux:select>
                </div>

                @if ($this->lockedInstitution)
                    <div class="flex items-center gap-3 rounded-lg border border-amber/30 bg-amber-bg px-4 py-3">
                        <flux:icon icon="lock-closed" variant="micro" class="size-5 shrink-0 text-amber" />
                        <flux:text class="text-brand-text!">
                            {{ __('Este reto es exclusivo para :institution.', ['institution' => $this->lockedInstitution->name]) }}
                        </flux:text>
                    </div>
                @else
                    <div>
                        <flux:label>{{ __('¿A quién va dirigido?') }}</flux:label>
                        <flux:radio.group wire:model.live="audienceScope" variant="segmented" class="mt-2">
                            <flux:radio value="all" icon="globe-alt" :label="__('Todas las instituciones')" />
                            <flux:radio value="specific" icon="building-office" :label="__('Instituciones específicas')" />
                        </flux:radio.group>

                        @if ($audienceScope === 'specific')
                            <div class="mt-3 space-y-3">
                                @if ($this->selectedInstitutions->isNotEmpty())
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($this->selectedInstitutions as $institution)
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-bg py-1 ps-3 pe-1.5 text-sm font-medium text-teal-deep">
                                                {{ $institution->name }}
                                                <button
                                                    type="button"
                                                    wire:click="removeInstitution('{{ $institution->uuid }}')"
                                                    class="flex size-4 items-center justify-center rounded-full hover:bg-teal-deep/10"
                                                    aria-label="{{ __('Quitar') }}"
                                                >
                                                    <flux:icon icon="x-mark" variant="micro" class="size-3" />
                                                </button>
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                                <flux:input wire:model.live="institutionSearch" icon="magnifying-glass" :placeholder="__('Buscar colegio por nombre...')" />

                                <div class="max-h-56 space-y-1 overflow-y-auto rounded-lg border border-zinc-200 p-2 dark:border-zinc-700">
                                    @forelse ($this->filteredInstitutions as $institution)
                                        <flux:checkbox wire:model="institutionUuids" value="{{ $institution->uuid }}" :label="$institution->name" class="p-2" />
                                    @empty
                                        <flux:text class="p-2 text-sm text-brand-text-muted!">{{ __('No se encontraron instituciones.') }}</flux:text>
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
                        <flux:heading size="lg">{{ __('Preguntas del reto') }}</flux:heading>
                        <flux:text class="text-sm text-brand-text-muted!">
                            {{ __('Cada pregunta puede ser de opción única, de opción múltiple (hasta 3) o pedir evidencia.') }}
                        </flux:text>
                    </div>
                </div>

                <span @class([
                    'whitespace-nowrap rounded-full px-3 py-1 text-xs font-bold uppercase',
                    'bg-red-100 text-red-700' => $this->questionsPointsTotal > $points,
                    'bg-teal-bg text-teal-deep' => $this->questionsPointsTotal <= $points,
                ])>
                    {{ __(':used / :cap puntos asignados', ['used' => $this->questionsPointsTotal, 'cap' => $points]) }}
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
                            <flux:badge variant="pill">{{ __('Pregunta :n', ['n' => $i + 1]) }}</flux:badge>
                            <flux:button size="sm" variant="danger" icon="trash" wire:click="removeQuestion({{ $i }})" type="button">
                                {{ __('Quitar') }}
                            </flux:button>
                        </div>

                        <div class="space-y-4">
                            <flux:input wire:model="questions.{{ $i }}.title" :label="__('Enunciado de la pregunta')" />
                            <flux:textarea wire:model="questions.{{ $i }}.description" :label="__('Instrucciones (opcional)')" rows="2" />

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <flux:input wire:model="questions.{{ $i }}.points" type="number" min="0" :label="__('Puntos')" />

                                <flux:radio.group wire:model.live="questions.{{ $i }}.answer_type" :label="__('Tipo de respuesta')" variant="segmented">
                                    <flux:radio value="choice" :label="__('Opción múltiple')" />
                                    <flux:radio value="evidence" :label="__('Evidencia')" />
                                </flux:radio.group>

                                <flux:checkbox wire:model="questions.{{ $i }}.is_scored" :label="__('Otorga puntos (tiene respuesta correcta)')" />
                            </div>

                            @if ($question['answer_type'] === 'choice')
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <flux:radio.group wire:model.live="questions.{{ $i }}.answer_mode" :label="__('Modo de selección')" variant="segmented">
                                        <flux:radio value="single" :label="__('Única (1 de N)')" />
                                        <flux:radio value="multiple" :label="__('Múltiple (hasta 3 de N)')" />
                                    </flux:radio.group>

                                    @if (($question['answer_mode'] ?? null) === 'multiple')
                                        <flux:input wire:model="questions.{{ $i }}.min_selections" type="number" min="1" max="3" :label="__('Mínimo de opciones correctas obligatorias')" />
                                    @endif

                                    @if ($question['is_scored'] ?? false)
                                        <flux:checkbox wire:model="questions.{{ $i }}.auto_verify" :label="__('Auto-verificar al responder (sin revisión del profesor)')" />
                                    @endif
                                </div>

                                <div class="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-800/50">
                                    <div class="mb-3 flex items-center justify-between">
                                        <flux:label>{{ __('Opciones de respuesta') }}</flux:label>
                                        <flux:button size="sm" icon="plus" wire:click="addOption({{ $i }})" type="button">
                                            {{ __('Agregar opción') }}
                                        </flux:button>
                                    </div>

                                    <div class="space-y-2">
                                        @foreach ($question['options'] as $j => $option)
                                            <div wire:key="question-{{ $i }}-option-{{ $j }}" class="flex items-center gap-2">
                                                @if (($question['answer_mode'] ?? null) === 'multiple')
                                                    <flux:checkbox wire:model="questions.{{ $i }}.options.{{ $j }}.is_correct" />
                                                @else
                                                    <button
                                                        type="button"
                                                        wire:click="selectSingleCorrectOption({{ $i }}, {{ $j }})"
                                                        class="flex size-5 shrink-0 items-center justify-center rounded-full border-2 {{ ($option['is_correct'] ?? false) ? 'border-teal-deep bg-teal-deep' : 'border-zinc-300 dark:border-zinc-600' }}"
                                                        aria-label="{{ __('Marcar como correcta') }}"
                                                    >
                                                        @if ($option['is_correct'] ?? false)
                                                            <span class="size-2 rounded-full bg-white"></span>
                                                        @endif
                                                    </button>
                                                @endif

                                                <flux:input wire:model="questions.{{ $i }}.options.{{ $j }}.label" :placeholder="__('Texto de la opción')" class="grow" />

                                                <flux:button size="sm" variant="ghost" icon="x-mark" wire:click="removeOption({{ $i }}, {{ $j }})" type="button" />
                                            </div>
                                        @endforeach
                                    </div>

                                    <flux:text class="mt-2 text-xs text-brand-text-muted!">
                                        {{ __('Única: marca la única opción correcta. Múltiple: marca 2 o 3 opciones correctas y define cuántas son obligatorias como mínimo.') }}
                                    </flux:text>
                                </div>
                            @else
                                <flux:text class="text-sm text-brand-text-muted!">
                                    {{ __('El usuario deberá adjuntar un archivo (foto, documento, PDF, etc.) y un profesor revisará y verificará manualmente esta pregunta.') }}
                                </flux:text>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <flux:button class="mt-5" icon="plus" wire:click="addQuestion" type="button">
                {{ __('Agregar pregunta') }}
            </flux:button>
        </div>

        <div class="sticky bottom-4 z-10 flex justify-end gap-2 rounded-xl border border-zinc-200 bg-white/95 p-4 shadow-lg backdrop-blur dark:border-zinc-700 dark:bg-zinc-900/95">
            <flux:button wire:click="cancel" type="button">{{ __('Cancelar') }}</flux:button>
            <flux:button wire:click="save" type="button" icon="document-check">
                {{ __('Guardar') }}
            </flux:button>
            <flux:button variant="primary" wire:click="publish" type="button" icon="megaphone" class="bg-teal! hover:bg-teal-deep!">
                {{ __('Publicar') }}
            </flux:button>
        </div>
    </form>
</section>
