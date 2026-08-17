<?php

namespace App\Livewire\Challenges;

use App\Models\Challenge;
use App\Models\ChallengeQuestion;
use App\Models\ChallengeQuestionAnswer;
use App\Models\Institution;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Gestionar retos')]
class ManageChallenges extends Component
{
    /**
     * Referencing a specific challenge for a privileged update; never trust this from the client.
     */
    #[Locked]
    public ?int $editingId = null;

    public string $target_role = 'student';

    public string $title = '';

    public string $description = '';

    public string $category = 'inclusion';

    public int $points = 100;

    public string $difficulty = 'easy';

    /**
     * 'all' publishes to every institution; 'specific' restricts to the picked institutionUuids.
     */
    public string $audienceScope = 'all';

    public array $institutionUuids = [];

    public string $institutionSearch = '';

    /**
     * Set only when arriving via ?institution=..., which forces this challenge to stay exclusive
     * to that institution; never trust this from the client since it overrides institutionUuids.
     */
    #[Locked]
    public ?string $lockedInstitutionUuid = null;

    /**
     * The lockedInstitutionUuid the page originally loaded with, used to restore it after a reset.
     */
    #[Locked]
    public ?string $initialLockedInstitutionUuid = null;

    /**
     * Each item: id, title, description, points, answer_type, answer_mode, min_selections,
     * is_scored, auto_verify, options[] (id, label, is_correct).
     *
     * @var array<int, array<string, mixed>>
     */
    public array $questions = [];

    public function mount(?Challenge $challenge = null): void
    {
        if ($preselected = request()->query('institution')) {
            $this->initialLockedInstitutionUuid = $preselected;
            $this->lockedInstitutionUuid = $preselected;
            $this->institutionUuids = [$preselected];
            $this->audienceScope = 'specific';
        }

        if ($challenge) {
            $this->authorize('update', $challenge);
            $this->loadChallenge($challenge);
        }
    }

    public function updatedAudienceScope(string $value): void
    {
        if ($value === 'all') {
            $this->institutionUuids = [];
            $this->institutionSearch = '';
        }
    }

    public function removeInstitution(string $uuid): void
    {
        $this->institutionUuids = array_values(array_diff($this->institutionUuids, [$uuid]));
    }

    #[Computed]
    public function institutions()
    {
        return Institution::orderBy('name')->get();
    }

    /**
     * The institution this challenge is locked to, shown as a read-only notice instead of the picker.
     */
    #[Computed]
    public function lockedInstitution(): ?Institution
    {
        return $this->lockedInstitutionUuid
            ? $this->institutions->firstWhere('uuid', $this->lockedInstitutionUuid)
            : null;
    }

    #[Computed]
    public function selectedInstitutions()
    {
        return $this->institutions->whereIn('uuid', $this->institutionUuids)->values();
    }

    #[Computed]
    public function filteredInstitutions()
    {
        $search = mb_strtolower(trim($this->institutionSearch));

        if ($search === '') {
            return $this->institutions;
        }

        return $this->institutions
            ->filter(fn (Institution $institution) => str_contains(mb_strtolower($institution->name), $search))
            ->values();
    }

    /**
     * Sum of points currently assigned to the in-progress questions, used to show the remaining cap live.
     */
    #[Computed]
    public function questionsPointsTotal(): int
    {
        return collect($this->questions)->sum(fn (array $question) => (int) ($question['points'] ?? 0));
    }

    protected function loadChallenge(Challenge $challenge): void
    {
        $challenge->loadMissing(['institutions', 'questions.options']);

        $this->editingId = $challenge->id;
        $this->target_role = $challenge->target_role;
        $this->title = $challenge->title;
        $this->description = $challenge->description;
        $this->category = $challenge->category;
        $this->points = $challenge->points;
        $this->difficulty = $challenge->difficulty;
        $this->lockedInstitutionUuid = null;
        $this->institutionUuids = $challenge->institutions->pluck('uuid')->all();
        $this->audienceScope = empty($this->institutionUuids) ? 'all' : 'specific';

        $this->questions = $challenge->questions->map(fn (ChallengeQuestion $question) => [
            'id' => $question->id,
            'title' => $question->title,
            'description' => $question->description,
            'points' => $question->points,
            'answer_type' => $question->answer_type,
            'answer_mode' => $question->answer_mode,
            'min_selections' => $question->min_selections,
            'is_scored' => $question->is_scored,
            'auto_verify' => $question->auto_verify,
            'options' => $question->options->map(fn ($option) => [
                'id' => $option->id,
                'label' => $option->label,
                'is_correct' => $option->is_correct,
            ])->all(),
        ])->all();
    }

    public function cancel(): void
    {
        $this->redirectRoute('challenges.manage', navigate: true);
    }

    public function addQuestion(): void
    {
        if (count($this->questions) >= 20) {
            return;
        }

        $this->questions[] = $this->defaultQuestion();
    }

    public function removeQuestion(int $index): void
    {
        unset($this->questions[$index]);
        $this->questions = array_values($this->questions);
    }

    public function addOption(int $questionIndex): void
    {
        if (! isset($this->questions[$questionIndex]) || count($this->questions[$questionIndex]['options']) >= 6) {
            return;
        }

        $this->questions[$questionIndex]['options'][] = $this->defaultOption();
    }

    public function removeOption(int $questionIndex, int $optionIndex): void
    {
        if (! isset($this->questions[$questionIndex]['options'][$optionIndex])) {
            return;
        }

        unset($this->questions[$questionIndex]['options'][$optionIndex]);
        $this->questions[$questionIndex]['options'] = array_values($this->questions[$questionIndex]['options']);
    }

    /**
     * In single-choice mode only one option may be correct; enforce that like a radio button.
     */
    public function selectSingleCorrectOption(int $questionIndex, int $optionIndex): void
    {
        if (! isset($this->questions[$questionIndex]['options'])) {
            return;
        }

        foreach ($this->questions[$questionIndex]['options'] as $i => $option) {
            $this->questions[$questionIndex]['options'][$i]['is_correct'] = ($i === $optionIndex);
        }
    }

    /**
     * Reset fields that no longer apply when a question's type/mode changes.
     */
    public function updatedQuestions(mixed $value, string $key): void
    {
        if (! str_ends_with($key, '.answer_type') && ! str_ends_with($key, '.answer_mode')) {
            return;
        }

        $index = (int) strtok($key, '.');

        if (! isset($this->questions[$index])) {
            return;
        }

        if (str_ends_with($key, '.answer_type') && $this->questions[$index]['answer_type'] === 'evidence') {
            $this->questions[$index]['answer_mode'] = null;
            $this->questions[$index]['min_selections'] = null;
            $this->questions[$index]['auto_verify'] = false;
        }

        if (str_ends_with($key, '.answer_mode') && $this->questions[$index]['answer_mode'] !== 'multiple') {
            $this->questions[$index]['min_selections'] = null;
        }
    }

    /**
     * Saving always keeps (or resets) the challenge as a draft; use publish() to make it live.
     */
    public function save(): void
    {
        $this->persist('draft');

        Flux::toast(variant: 'success', text: __('Reto guardado como borrador.'));
        $this->redirectRoute('challenges.manage', navigate: true);
    }

    public function publish(): void
    {
        $this->persist('published');

        Flux::toast(variant: 'success', text: __('Reto publicado.'));
        $this->redirectRoute('challenges.manage', navigate: true);
    }

    protected function persist(string $status): void
    {
        $challenge = $this->editingId ? Challenge::findOrFail($this->editingId) : null;
        $this->authorize($challenge ? 'update' : 'create', $challenge ?? Challenge::class);

        // The locked institution (or the "all" scope) is authoritative; never trust a tampered institutionUuids array.
        if ($this->lockedInstitutionUuid) {
            $this->institutionUuids = [$this->lockedInstitutionUuid];
        } elseif ($this->audienceScope === 'all') {
            $this->institutionUuids = [];
        }

        $data = $this->validate($this->rules(), $this->messages());
        $this->validateQuestionRules($data['questions'] ?? []);

        $data['status'] = $status;

        $institutionIds = Institution::whereIn('uuid', $data['institutionUuids'])->pluck('id');
        $questions = $data['questions'] ?? [];
        unset($data['institutionUuids'], $data['questions']);

        DB::transaction(function () use (&$challenge, $data, $institutionIds, $questions) {
            if ($challenge) {
                $challenge->update($data);
            } else {
                $challenge = Challenge::create($data + ['created_by' => Auth::id()]);
            }

            $challenge->institutions()->sync($institutionIds);
            $this->syncQuestions($challenge, $questions);
        });
    }

    protected function rules(): array
    {
        return [
            'target_role' => ['required', Rule::in(['student', 'teacher', 'guardian'])],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'category' => ['required', 'string', 'max:255'],
            'points' => ['required', 'integer', 'min:1', 'max:100000'],
            'difficulty' => ['required', Rule::in(['easy', 'medium', 'hard'])],
            'institutionUuids' => ['array', 'max:100'],
            'institutionUuids.*' => ['string', 'exists:institutions,uuid'],
            'questions' => ['required', 'array', 'min:1', 'max:20'],
            'questions.*.id' => ['nullable', 'integer'],
            'questions.*.title' => ['required', 'string', 'max:255'],
            'questions.*.description' => ['nullable', 'string', 'max:2000'],
            'questions.*.points' => ['required', 'integer', 'min:0', 'max:100000'],
            'questions.*.answer_type' => ['required', Rule::in(['choice', 'evidence'])],
            'questions.*.answer_mode' => ['nullable', Rule::in(['single', 'multiple'])],
            'questions.*.min_selections' => ['nullable', 'integer', 'min:1', 'max:3'],
            'questions.*.is_scored' => ['boolean'],
            'questions.*.auto_verify' => ['boolean'],
            'questions.*.options' => ['array', 'max:6'],
            'questions.*.options.*.id' => ['nullable', 'integer'],
            'questions.*.options.*.label' => ['required', 'string', 'max:255'],
            'questions.*.options.*.is_correct' => ['boolean'],
        ];
    }

    protected function messages(): array
    {
        return [
            'questions.required' => __('Agrega al menos una pregunta al reto.'),
            'questions.min' => __('Agrega al menos una pregunta al reto.'),
        ];
    }

    /**
     * Business rules that go beyond simple field validation: the points cap and the
     * single/multiple choice/correctness rules confirmed for this feature.
     */
    protected function validateQuestionRules(array $questions): void
    {
        $errors = [];
        $totalPoints = collect($questions)->sum('points');

        if ($totalPoints > $this->points) {
            $errors['points'] = [__('La suma de puntos de las preguntas (:total) supera el tope del reto (:cap).', ['total' => $totalPoints, 'cap' => $this->points])];
        }

        foreach ($questions as $i => $question) {
            if ($question['answer_type'] !== 'choice') {
                continue;
            }

            $options = $question['options'] ?? [];

            if (! in_array($question['answer_mode'] ?? null, ['single', 'multiple'], true)) {
                $errors["questions.$i.answer_mode"] = [__('Selecciona si la pregunta es de respuesta única o múltiple.')];

                continue;
            }

            if (count($options) < 2) {
                $errors["questions.$i.options"] = [__('Agrega al menos 2 opciones.')];

                continue;
            }

            $correctCount = collect($options)->where('is_correct', true)->count();

            if ($question['answer_mode'] === 'single' && $correctCount !== 1) {
                $errors["questions.$i.options"] = [__('Marca exactamente una opción correcta.')];
            }

            if ($question['answer_mode'] === 'multiple') {
                if ($correctCount < 2 || $correctCount > 3) {
                    $errors["questions.$i.options"] = [__('Marca entre 2 y 3 opciones correctas.')];
                } elseif (($question['min_selections'] ?? null) === null || $question['min_selections'] > $correctCount) {
                    $errors["questions.$i.min_selections"] = [__('El mínimo de selecciones debe estar entre 1 y :max.', ['max' => $correctCount])];
                }
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Persist the submitted questions/options. Any submitted "id" that doesn't actually
     * belong to this challenge (or question) is ignored and treated as a new record,
     * so a tampered id can never be used to hijack another challenge's data.
     */
    protected function syncQuestions(Challenge $challenge, array $questions): void
    {
        $ownedIds = $challenge->questions()->pluck('id')->all();
        $keptIds = [];

        foreach ($questions as $questionData) {
            $optionsData = $questionData['options'] ?? [];
            unset($questionData['options']);

            $questionId = $questionData['id'] ?? null;
            unset($questionData['id']);

            if ($questionData['answer_type'] !== 'choice') {
                $questionData['answer_mode'] = null;
                $questionData['min_selections'] = null;
                $optionsData = [];
            } elseif ($questionData['answer_mode'] !== 'multiple') {
                $questionData['min_selections'] = null;
            }

            if ($questionId && in_array($questionId, $ownedIds, true)) {
                $question = $challenge->questions()->findOrFail($questionId);
                $question->update($questionData);
            } else {
                $question = $challenge->questions()->create($questionData);
            }

            $keptIds[] = $question->id;
            $this->syncOptions($question, $optionsData);
        }

        $removableIds = $challenge->questions()->whereNotIn('id', $keptIds)->pluck('id');

        if ($removableIds->isNotEmpty()) {
            $hasAnswers = ChallengeQuestionAnswer::whereIn('challenge_question_id', $removableIds)->exists();

            if ($hasAnswers) {
                throw ValidationException::withMessages([
                    'questions' => [__('No puedes eliminar preguntas que ya tienen respuestas registradas.')],
                ]);
            }

            $challenge->questions()->whereIn('id', $removableIds)->delete();
        }
    }

    protected function syncOptions(ChallengeQuestion $question, array $optionsData): void
    {
        $ownedIds = $question->options()->pluck('id')->all();
        $keptIds = [];

        foreach ($optionsData as $optionData) {
            $optionId = $optionData['id'] ?? null;
            unset($optionData['id']);

            if ($optionId && in_array($optionId, $ownedIds, true)) {
                $option = $question->options()->findOrFail($optionId);
                $option->update($optionData);
            } else {
                $option = $question->options()->create($optionData);
            }

            $keptIds[] = $option->id;
        }

        $question->options()->whereNotIn('id', $keptIds)->delete();
    }

    protected function defaultQuestion(): array
    {
        return [
            'id' => null,
            'title' => '',
            'description' => '',
            'points' => 0,
            'answer_type' => 'choice',
            'answer_mode' => 'single',
            'min_selections' => null,
            'is_scored' => true,
            'auto_verify' => true,
            'options' => [$this->defaultOption(), $this->defaultOption()],
        ];
    }

    protected function defaultOption(): array
    {
        return ['id' => null, 'label' => '', 'is_correct' => false];
    }

    public function render()
    {
        return view('livewire.challenges.manage-challenges');
    }
}
