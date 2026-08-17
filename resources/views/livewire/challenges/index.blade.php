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
                                <flux:modal.trigger name="challenge-detail">
                                    <button type="button" wire:click="view('{{ $challenge->ulid }}')" class="font-mono text-xs font-bold text-teal-deep hover:underline dark:text-teal">
                                        {{ $challenge->code }}
                                    </button>
                                </flux:modal.trigger>
                            </td>
                            <td class="px-4 py-3">
                                <flux:modal.trigger name="challenge-detail">
                                    <button type="button" wire:click="view('{{ $challenge->ulid }}')" class="text-left font-medium text-brand-text hover:text-teal-deep hover:underline">
                                        {{ $challenge->title }}
                                    </button>
                                </flux:modal.trigger>
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
                                    <flux:modal.trigger name="challenge-detail">
                                        <flux:button size="sm" icon="eye" wire:click="view('{{ $challenge->ulid }}')">
                                            {{ __('Ver detalle') }}
                                        </flux:button>
                                    </flux:modal.trigger>

                                    @can('update-challenge')
                                        <flux:button size="sm" icon="pencil-square" :href="route('challenges.manage.edit', $challenge->ulid)" wire:navigate>
                                            {{ __('Editar') }}
                                        </flux:button>
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
        <div wire:loading wire:target="view" class="space-y-6">
            <flux:skeleton.group animate="pulse" class="space-y-6">
                <div class="flex items-start justify-between gap-3">
                    <div class="space-y-2">
                        <flux:skeleton class="h-5 w-20" />
                        <flux:skeleton class="h-7 w-56" />
                        <flux:skeleton class="h-4 w-32" />
                    </div>
                    <flux:skeleton class="h-6 w-24" />
                </div>

                <flux:skeleton class="h-4 w-full" />
                <flux:skeleton class="h-4 w-4/5" />

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    @for ($i = 0; $i < 6; $i++)
                        <flux:skeleton class="h-10 w-full" />
                    @endfor
                </div>

                <div class="space-y-3">
                    <flux:skeleton class="h-5 w-28" />
                    <flux:skeleton class="h-20 w-full" />
                    <flux:skeleton class="h-20 w-full" />
                </div>
            </flux:skeleton.group>
        </div>

        <div wire:loading.remove wire:target="view">
        @if ($this->viewingChallenge)
            @php($challenge = $this->viewingChallenge)

            <div class="space-y-6">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <flux:badge size="sm" class="mb-2 font-mono">{{ $challenge->code }}</flux:badge>
                        <flux:heading size="lg">{{ $challenge->title }}</flux:heading>
                        <flux:text class="text-sm text-brand-text-muted!">{{ $challenge->category }}</flux:text>
                    </div>
                    <flux:badge :color="match ($challenge->status) {
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
                </div>

                <flux:text>{{ $challenge->description }}</flux:text>

                <div class="grid grid-cols-2 gap-3 rounded-lg bg-zinc-50 p-4 text-sm sm:grid-cols-3 dark:bg-zinc-800/50">
                    <div>
                        <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Dirigido a') }}</div>
                        <div class="font-medium text-brand-text">
                            {{ __(match ($challenge->target_role) {
                                'student' => 'Estudiante',
                                'teacher' => 'Profesor',
                                'guardian' => 'Acudiente',
                            }) }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Dificultad') }}</div>
                        <div class="font-medium text-brand-text">
                            {{ __(match ($challenge->difficulty) {
                                'easy' => 'Fácil',
                                'medium' => 'Media',
                                'hard' => 'Difícil',
                            }) }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Tope de puntos') }}</div>
                        <div class="font-medium text-brand-text">{{ $challenge->points }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Preguntas') }}</div>
                        <div class="font-medium text-brand-text">{{ $challenge->questions->count() }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Envíos') }}</div>
                        <div class="font-medium text-brand-text">{{ $challenge->completions_count }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Verificados') }}</div>
                        <div class="font-medium text-brand-text">{{ $challenge->verified_completions_count }}</div>
                    </div>
                    <div class="col-span-2 sm:col-span-3">
                        <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Instituciones') }}</div>
                        <div class="font-medium text-brand-text">
                            {{ $challenge->institutions->isEmpty() ? __('Todas las instituciones') : $challenge->institutions->pluck('name')->join(', ') }}
                        </div>
                    </div>
                    <div class="col-span-2 sm:col-span-3">
                        <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Creado por') }}</div>
                        <div class="font-medium text-brand-text">{{ $challenge->creator?->name ?? __('—') }}</div>
                    </div>
                </div>

                <div>
                    <flux:heading size="sm" class="mb-3">{{ __('Preguntas') }}</flux:heading>

                    <div class="space-y-3">
                        @forelse ($challenge->questions as $question)
                            <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                                <div class="mb-2 flex items-start justify-between gap-2">
                                    <div>
                                        <flux:badge size="sm" class="mb-1 font-mono">{{ $question->code }}</flux:badge>
                                        <div class="font-medium text-brand-text">{{ $question->title }}</div>
                                        @if ($question->description)
                                            <flux:text class="text-sm text-brand-text-muted!">{{ $question->description }}</flux:text>
                                        @endif
                                    </div>
                                    <flux:badge size="sm" variant="pill">{{ __(':n pts', ['n' => $question->points]) }}</flux:badge>
                                </div>

                                @if ($question->answer_type === 'choice')
                                    <flux:text class="mb-2 text-xs text-brand-text-muted!">
                                        {{ $question->answer_mode === 'single' ? __('Opción única') : __('Opción múltiple · mínimo :n correctas', ['n' => $question->min_selections]) }}
                                        @unless ($question->is_scored) · {{ __('sin puntaje') }} @endunless
                                    </flux:text>

                                    <ul class="space-y-1">
                                        @foreach ($question->options as $option)
                                            <li class="flex items-center gap-2 text-sm">
                                                <flux:icon
                                                    :icon="$option->is_correct ? 'check-circle' : 'x-circle'"
                                                    variant="micro"
                                                    class="size-4 shrink-0 {{ $option->is_correct ? 'text-teal-deep' : 'text-zinc-300 dark:text-zinc-600' }}"
                                                />
                                                <span class="{{ $option->is_correct ? 'font-medium text-brand-text' : 'text-brand-text-muted' }}">
                                                    {{ $option->label }}
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <flux:text class="text-xs text-brand-text-muted!">
                                        {{ __('Requiere evidencia adjunta (foto, documento, PDF, etc.) y verificación manual.') }}
                                    </flux:text>
                                @endif
                            </div>
                        @empty
                            <flux:text class="text-brand-text-muted!">{{ __('Este reto todavía no tiene preguntas.') }}</flux:text>
                        @endforelse
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <flux:modal.close>
                        <flux:button>{{ __('Cerrar') }}</flux:button>
                    </flux:modal.close>

                    @can('update-challenge')
                        <flux:button variant="primary" icon="pencil-square" class="bg-teal! hover:bg-teal-deep!" :href="route('challenges.manage.edit', $challenge->ulid)" wire:navigate>
                            {{ __('Editar reto') }}
                        </flux:button>
                    @endcan
                </div>
            </div>
        @endif
        </div>
    </flux:modal>
</section>
