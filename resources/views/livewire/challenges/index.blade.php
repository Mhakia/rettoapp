<section class="mx-auto w-full max-w-6xl pb-16">
    <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
        <div class="rounded-xl border border-teal-border bg-teal-bg px-6 py-5">
            <flux:heading size="xl" class="text-teal-deep!">{{ __('Gestionar retos') }}</flux:heading>
            <flux:text class="text-brand-text-muted!">
                {{ __('Consulta, busca y administra todos los retos de la plataforma.') }}
            </flux:text>
        </div>

        @can('create-challenge')
            <flux:button variant="primary" icon="plus" class="bg-teal! hover:bg-teal-deep!" :href="route('challenges.manage.create')" wire:navigate>
                {{ __('Crear reto') }}
            </flux:button>
        @endcan
    </div>

    <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-xl border border-zinc-200 bg-white p-4 text-center shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-2xl font-extrabold text-brand-text">{{ $this->stats['total'] }}</div>
            <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Total') }}</div>
        </div>
        <div class="rounded-xl border border-teal-border bg-teal-bg p-4 text-center">
            <div class="text-2xl font-extrabold text-teal-deep">{{ $this->stats['published'] }}</div>
            <div class="text-xs font-semibold text-teal-deep uppercase">{{ __('Publicados') }}</div>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 text-center shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-2xl font-extrabold text-brand-text">{{ $this->stats['draft'] }}</div>
            <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Borradores') }}</div>
        </div>
        <div class="rounded-xl border border-amber/30 bg-amber-bg p-4 text-center">
            <div class="text-2xl font-extrabold text-amber">{{ $this->stats['archived'] }}</div>
            <div class="text-xs font-semibold text-amber uppercase">{{ __('Archivados') }}</div>
        </div>
    </div>

    <div
        wire:key="challenges-table-{{ $this->challengesCacheKey }}"
        x-data="{
            selected: null,
            details: @js($this->challengeDetails),
            roleLabels: @js(['student' => __('Estudiante'), 'teacher' => __('Profesor'), 'guardian' => __('Acudiente')]),
            statusLabels: @js(['draft' => __('Borrador'), 'published' => __('Publicado'), 'archived' => __('Archivado')]),
            difficultyLabels: @js(['easy' => __('Fácil'), 'medium' => __('Media'), 'hard' => __('Difícil')]),
            roleColors: { student: 'text-blue-700 bg-blue-400/20 dark:text-blue-200 dark:bg-blue-400/40', teacher: 'text-purple-700 bg-purple-400/20 dark:text-purple-200 dark:bg-purple-400/40', guardian: 'text-amber-700 bg-amber-400/20 dark:text-amber-200 dark:bg-amber-400/40' },
            statusColors: { draft: 'text-zinc-700 bg-zinc-400/15 dark:text-zinc-200 dark:bg-zinc-400/40', published: 'text-teal-700 bg-teal-400/20 dark:text-teal-200 dark:bg-teal-400/40', archived: 'text-orange-700 bg-orange-400/20 dark:text-orange-200 dark:bg-orange-400/40' },
            difficultyColors: { easy: 'text-green-700 bg-green-400/20 dark:text-green-200 dark:bg-green-400/40', medium: 'text-amber-700 bg-amber-400/20 dark:text-amber-200 dark:bg-amber-400/40', hard: 'text-red-700 bg-red-400/20 dark:text-red-200 dark:bg-red-400/40' },
            show(ulid) { this.selected = this.details[ulid]; $dispatch('modal-show', { name: 'challenge-detail' }); },
        }"
    >
    <div class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex flex-wrap items-center gap-3 border-b border-zinc-100 p-4 dark:border-zinc-800">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" :placeholder="__('Buscar por código, título o categoría...')" class="min-w-64 grow" />

            <flux:select wire:model.live="roleFilter" class="w-44">
                <flux:select.option value="">{{ __('Todos los roles') }}</flux:select.option>
                <flux:select.option value="student">{{ __('Estudiante') }}</flux:select.option>
                <flux:select.option value="teacher">{{ __('Profesor') }}</flux:select.option>
                <flux:select.option value="guardian">{{ __('Acudiente') }}</flux:select.option>
            </flux:select>

            <flux:select wire:model.live="statusFilter" class="w-44">
                <flux:select.option value="">{{ __('Todos los estados') }}</flux:select.option>
                <flux:select.option value="draft">{{ __('Borrador') }}</flux:select.option>
                <flux:select.option value="published">{{ __('Publicado') }}</flux:select.option>
                <flux:select.option value="archived">{{ __('Archivado') }}</flux:select.option>
            </flux:select>

            @if ($search !== '' || $roleFilter !== '' || $statusFilter !== '')
                <flux:button size="sm" variant="ghost" icon="x-mark" wire:click="$set('search', ''); $set('roleFilter', ''); $set('statusFilter', '')">
                    {{ __('Limpiar filtros') }}
                </flux:button>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-100 text-left text-xs font-semibold text-brand-text-muted uppercase dark:border-zinc-800">
                        <th class="px-4 py-3">{{ __('Código') }}</th>
                        <th class="px-4 py-3">{{ __('Reto') }}</th>
                        <th class="px-4 py-3">{{ __('Dirigido a') }}</th>
                        <th class="px-4 py-3">{{ __('Estado') }}</th>
                        <th class="px-4 py-3">{{ __('Dificultad') }}</th>
                        <th class="px-4 py-3 text-center">{{ __('Puntos') }}</th>
                        <th class="px-4 py-3 text-center">{{ __('Preguntas') }}</th>
                        <th class="px-4 py-3">{{ __('Instituciones') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('Acciones') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($this->challenges as $challenge)
                        <tr class="transition hover:bg-zinc-50 dark:hover:bg-white/5">
                            <td class="px-4 py-3">
                                <button type="button" x-on:click="show('{{ $challenge->ulid }}')" class="font-mono text-xs font-bold text-teal-deep hover:underline dark:text-teal">
                                    {{ $challenge->code }}
                                </button>
                            </td>
                            <td class="px-4 py-3">
                                <button type="button" x-on:click="show('{{ $challenge->ulid }}')" class="text-left font-medium text-brand-text hover:text-teal-deep hover:underline">
                                    {{ $challenge->title }}
                                </button>
                                <div class="text-xs text-brand-text-muted!">{{ $challenge->category }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <flux:badge size="sm" :color="match ($challenge->target_role) {
                                    'student' => 'blue',
                                    'teacher' => 'purple',
                                    'guardian' => 'amber',
                                }">
                                    {{ __(match ($challenge->target_role) {
                                        'student' => 'Estudiante',
                                        'teacher' => 'Profesor',
                                        'guardian' => 'Acudiente',
                                    }) }}
                                </flux:badge>
                            </td>
                            <td class="px-4 py-3">
                                <flux:badge size="sm" :color="match ($challenge->status) {
                                    'draft' => 'zinc',
                                    'published' => 'teal',
                                    'archived' => 'orange',
                                }">
                                    {{ __(match ($challenge->status) {
                                        'draft' => 'Borrador',
                                        'published' => 'Publicado',
                                        'archived' => 'Archivado',
                                    }) }}
                                </flux:badge>
                            </td>
                            <td class="px-4 py-3">
                                <flux:badge size="sm" :color="match ($challenge->difficulty) {
                                    'easy' => 'green',
                                    'medium' => 'amber',
                                    'hard' => 'red',
                                }">
                                    {{ __(match ($challenge->difficulty) {
                                        'easy' => 'Fácil',
                                        'medium' => 'Media',
                                        'hard' => 'Difícil',
                                    }) }}
                                </flux:badge>
                            </td>
                            <td class="px-4 py-3 text-center font-semibold text-brand-text">{{ $challenge->points }}</td>
                            <td class="px-4 py-3 text-center">
                                <flux:badge size="sm" variant="pill">{{ $challenge->questions_count }}</flux:badge>
                            </td>
                            <td class="px-4 py-3 text-xs text-brand-text-muted!">
                                {{ $challenge->institutions->isEmpty() ? __('Todas') : $challenge->institutions->pluck('name')->join(', ') }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-1.5">
                                    <flux:button size="sm" icon="eye" :tooltip="__('Ver detalle')" x-on:click="show('{{ $challenge->ulid }}')" />

                                    @can('update-challenge')
                                        <flux:button size="sm" icon="pencil-square" :tooltip="__('Editar')" :href="route('challenges.manage.edit', $challenge->ulid)" wire:navigate />

                                        @if ($challenge->status !== 'archived')
                                            <flux:button size="sm" icon="archive-box" :tooltip="__('Archivar')" wire:click="archive('{{ $challenge->ulid }}')" wire:confirm="{{ __('¿Archivar este reto?') }}" />
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-10 text-center text-brand-text-muted!">
                                {{ __('No se encontraron retos con esos criterios.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->challenges->hasPages())
            <div class="border-t border-zinc-100 p-4 dark:border-zinc-800">
                {{ $this->challenges->links() }}
            </div>
        @endif
    </div>

    <flux:modal name="challenge-detail" class="w-full max-w-2xl">
        <template x-if="selected">
            <div class="space-y-6">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <span class="mb-2 inline-block rounded-md bg-zinc-400/15 px-2 py-1 font-mono text-xs font-medium text-zinc-700 dark:bg-zinc-400/40 dark:text-zinc-200" x-text="selected.code"></span>
                        <flux:heading size="lg" x-text="selected.title"></flux:heading>
                        <flux:text class="text-sm text-brand-text-muted!" x-text="selected.category"></flux:text>
                    </div>
                    <span class="rounded-md px-2 py-1 text-xs font-medium" :class="statusColors[selected.status]" x-text="statusLabels[selected.status]"></span>
                </div>

                <flux:text x-text="selected.description"></flux:text>

                <div class="grid grid-cols-2 gap-3 rounded-lg bg-zinc-50 p-4 text-sm sm:grid-cols-3 dark:bg-zinc-800/50">
                    <div>
                        <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Dirigido a') }}</div>
                        <div class="font-medium text-brand-text" x-text="roleLabels[selected.target_role]"></div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Dificultad') }}</div>
                        <div class="font-medium text-brand-text" x-text="difficultyLabels[selected.difficulty]"></div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Tope de puntos') }}</div>
                        <div class="font-medium text-brand-text" x-text="selected.points"></div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Preguntas') }}</div>
                        <div class="font-medium text-brand-text" x-text="selected.questions.length"></div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Envíos') }}</div>
                        <div class="font-medium text-brand-text" x-text="selected.completions_count"></div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Verificados') }}</div>
                        <div class="font-medium text-brand-text" x-text="selected.verified_completions_count"></div>
                    </div>
                    <div class="col-span-2 sm:col-span-3">
                        <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Instituciones') }}</div>
                        <div class="font-medium text-brand-text" x-text="selected.institutions.length ? selected.institutions.join(', ') : '{{ __('Todas las instituciones') }}'"></div>
                    </div>
                    <div class="col-span-2 sm:col-span-3">
                        <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Creado por') }}</div>
                        <div class="font-medium text-brand-text" x-text="selected.creator_name || '—'"></div>
                    </div>
                </div>

                <div>
                    <flux:heading size="sm" class="mb-3">{{ __('Preguntas') }}</flux:heading>

                    <div class="space-y-3">
                        <template x-for="question in selected.questions" :key="question.code">
                            <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                                <div class="mb-2 flex items-start justify-between gap-2">
                                    <div>
                                        <span class="mb-1 inline-block rounded-md bg-zinc-400/15 px-2 py-1 font-mono text-xs font-medium text-zinc-700 dark:bg-zinc-400/40 dark:text-zinc-200" x-text="question.code"></span>
                                        <div class="font-medium text-brand-text" x-text="question.title"></div>
                                        <flux:text class="text-sm text-brand-text-muted!" x-show="question.description" x-text="question.description"></flux:text>
                                    </div>
                                    <span class="rounded-full bg-zinc-400/15 px-3 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-400/40 dark:text-zinc-200" x-text="question.points + ' {{ __('pts') }}'"></span>
                                </div>

                                <template x-if="question.answer_type === 'choice'">
                                    <div>
                                        <flux:text class="mb-2 text-xs text-brand-text-muted!">
                                            <span x-text="question.answer_mode === 'single' ? '{{ __('Opción única') }}' : ('{{ __('Opción múltiple · mínimo') }} ' + question.min_selections + ' {{ __('correctas') }}')"></span>
                                            <span x-show="!question.is_scored"> · {{ __('sin puntaje') }}</span>
                                        </flux:text>

                                        <ul class="space-y-1">
                                            <template x-for="option in question.options" :key="option.label">
                                                <li class="flex items-center gap-2 text-sm">
                                                    <flux:icon icon="check-circle" variant="micro" class="size-4 shrink-0 text-teal-deep" x-show="option.is_correct" />
                                                    <flux:icon icon="x-circle" variant="micro" class="size-4 shrink-0 text-zinc-300 dark:text-zinc-600" x-show="!option.is_correct" />
                                                    <span :class="option.is_correct ? 'font-medium text-brand-text' : 'text-brand-text-muted'" x-text="option.label"></span>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                </template>

                                <template x-if="question.answer_type !== 'choice'">
                                    <flux:text class="text-xs text-brand-text-muted!">
                                        {{ __('Requiere evidencia adjunta (foto, documento, PDF, etc.) y verificación manual.') }}
                                    </flux:text>
                                </template>
                            </div>
                        </template>

                        <flux:text x-show="selected.questions.length === 0" class="text-brand-text-muted!">{{ __('Este reto todavía no tiene preguntas.') }}</flux:text>
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <flux:modal.close>
                        <flux:button>{{ __('Cerrar') }}</flux:button>
                    </flux:modal.close>

                    @can('update-challenge')
                        <flux:button variant="primary" icon="pencil-square" class="bg-teal! hover:bg-teal-deep!" href="#" x-bind:href="selected.edit_url" wire:navigate>
                            {{ __('Editar reto') }}
                        </flux:button>
                    @endcan
                </div>
            </div>
        </template>
    </flux:modal>
    </div>
</section>
