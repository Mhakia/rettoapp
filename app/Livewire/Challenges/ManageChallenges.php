<?php

namespace App\Livewire\Challenges;

use App\Models\Challenge;
use App\Models\Institution;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Gestionar retos')]
class ManageChallenges extends Component
{
    public ?int $editingId = null;

    public string $target_role = 'student';

    public string $title = '';

    public string $description = '';

    public string $category = 'inclusion';

    public int $points = 10;

    public string $difficulty = 'easy';

    public string $status = 'draft';

    public array $institutionUuids = [];

    #[Computed]
    public function challenges()
    {
        return Challenge::with('institutions')->latest()->get();
    }

    #[Computed]
    public function institutions()
    {
        return Institution::orderBy('name')->get();
    }

    public function edit(string $challengeUlid): void
    {
        $challenge = Challenge::with('institutions')->where('ulid', $challengeUlid)->firstOrFail();
        $this->authorize('update', $challenge);

        $this->editingId = $challenge->id;
        $this->target_role = $challenge->target_role;
        $this->title = $challenge->title;
        $this->description = $challenge->description;
        $this->category = $challenge->category;
        $this->points = $challenge->points;
        $this->difficulty = $challenge->difficulty;
        $this->status = $challenge->status;
        $this->institutionUuids = $challenge->institutions->pluck('uuid')->all();
    }

    public function save(): void
    {
        $challenge = $this->editingId ? Challenge::findOrFail($this->editingId) : null;
        $this->authorize($challenge ? 'update' : 'create', $challenge ?? Challenge::class);

        $data = $this->validate([
            'target_role' => ['required', Rule::in(['student', 'teacher', 'guardian'])],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category' => ['required', 'string', 'max:255'],
            'points' => ['required', 'integer', 'min:1'],
            'difficulty' => ['required', Rule::in(['easy', 'medium', 'hard'])],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'institutionUuids' => ['array'],
            'institutionUuids.*' => ['exists:institutions,uuid'],
        ]);

        $institutionIds = Institution::whereIn('uuid', $data['institutionUuids'])->pluck('id');
        unset($data['institutionUuids']);

        if ($challenge) {
            $challenge->update($data);
        } else {
            $challenge = Challenge::create($data + ['created_by' => Auth::id()]);
        }

        $challenge->institutions()->sync($institutionIds);

        $this->reset(['editingId', 'target_role', 'title', 'description', 'category', 'points', 'difficulty', 'status', 'institutionUuids']);
        $this->target_role = 'student';
        $this->category = 'inclusion';
        $this->points = 10;
        $this->difficulty = 'easy';
        $this->status = 'draft';

        unset($this->challenges);

        Flux::toast(variant: 'success', text: __('Reto guardado.'));
    }

    public function render()
    {
        return view('livewire.challenges.manage-challenges');
    }
}
