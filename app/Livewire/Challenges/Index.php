<?php

namespace App\Livewire\Challenges;

use App\Models\Challenge;
use Flux\Flux;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Gestionar retos')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $roleFilter = '';

    public string $statusFilter = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRoleFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function archive(string $ulid): void
    {
        $challenge = Challenge::where('ulid', $ulid)->firstOrFail();
        $this->authorize('update', $challenge);

        $challenge->update(['status' => 'archived']);

        unset($this->challenges, $this->challengeDetails, $this->challengesCacheKey, $this->stats);

        Flux::toast(variant: 'success', text: __('challenge_archived'));
    }

    /**
     * Keyset (cursor) pagination stays fast no matter how large the table grows, unlike
     * offset pagination which gets slower for deeper pages; ordering by the indexed primary
     * key keeps it deterministic without needing an index on created_at.
     */
    #[Computed]
    public function challenges()
    {
        $search = trim($this->search);

        return Challenge::query()
            ->withCount([
                'questions',
                'completions',
                'completions as verified_completions_count' => fn ($query) => $query->where('status', 'verified'),
            ])
            ->with(['institutions', 'creator', 'questions.options'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    // ILIKE (case-insensitive) is accelerated by the pg_trgm GIN indexes on these columns.
                    $q->where('title', 'ilike', "%{$search}%")
                        ->orWhere('category', 'ilike', "%{$search}%");

                    // Accept searches like "R-7", "r7" or plain "7"/"0007" against the numeric id.
                    if (preg_match('/^r-?0*(\d+)$/i', $search, $matches)) {
                        $q->orWhere('id', (int) $matches[1]);
                    } elseif (ctype_digit($search)) {
                        $q->orWhere('id', (int) $search);
                    }
                });
            })
            ->when($this->roleFilter !== '', fn ($query) => $query->where('target_role', $this->roleFilter))
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
            ->orderByDesc('id')
            ->cursorPaginate(10);
    }

    /**
     * A key that changes whenever the current page's set of challenges changes, used to force
     * Alpine to reinitialize with fresh `challengeDetails` instead of keeping stale client state.
     */
    #[Computed]
    public function challengesCacheKey(): string
    {
        return md5($this->challenges->pluck('ulid')->implode(','));
    }

    /**
     * Full detail (including questions/options) for every row on the current page, embedded
     * once in the page so the detail popup opens instantly client-side with zero extra
     * requests, regardless of how many millions of challenges the table holds.
     *
     * @return array<string, array<string, mixed>>
     */
    #[Computed]
    public function challengeDetails(): array
    {
        return $this->challenges->getCollection()->mapWithKeys(fn (Challenge $challenge) => [
            $challenge->ulid => [
                'code' => $challenge->code,
                'title' => $challenge->title,
                'category' => $challenge->category,
                'description' => $challenge->description,
                'status' => $challenge->status,
                'target_role' => $challenge->target_role,
                'difficulty' => $challenge->difficulty,
                'points' => $challenge->points,
                'completions_count' => $challenge->completions_count,
                'verified_completions_count' => $challenge->verified_completions_count,
                'institutions' => $challenge->institutions->pluck('name')->all(),
                'creator_name' => $challenge->creator?->name,
                'edit_url' => route('challenges.manage.edit', $challenge->ulid),
                'questions' => $challenge->questions->map(fn ($question) => [
                    'code' => $question->code,
                    'title' => $question->title,
                    'description' => $question->description,
                    'points' => $question->points,
                    'answer_type' => $question->answer_type,
                    'answer_mode' => $question->answer_mode,
                    'min_selections' => $question->min_selections,
                    'scoring_mode' => $question->scoring_mode,
                    'options' => $question->options->map(fn ($option) => [
                        'label' => $option->label,
                        'is_correct' => $option->is_correct,
                    ])->all(),
                ])->all(),
            ],
        ])->all();
    }

    /**
     * @return array<string, int>
     */
    #[Computed]
    public function stats(): array
    {
        return Cache::remember('challenges.index.stats', 60, function () {
            $row = Challenge::query()
                ->selectRaw('count(*) as total')
                ->selectRaw("sum(case when status = 'published' then 1 else 0 end) as published")
                ->selectRaw("sum(case when status = 'draft' then 1 else 0 end) as draft")
                ->selectRaw("sum(case when status = 'archived' then 1 else 0 end) as archived")
                ->first();

            return [
                'total' => (int) $row->total,
                'published' => (int) $row->published,
                'draft' => (int) $row->draft,
                'archived' => (int) $row->archived,
            ];
        });
    }

    public function render()
    {
        return view('livewire.challenges.index');
    }
}
