<?php

namespace App\Livewire\Challenges;

use App\Models\Challenge;
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

    /**
     * ulid of the challenge currently shown in the detail modal.
     */
    public ?string $viewingUlid = null;

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

    public function view(string $ulid): void
    {
        $this->viewingUlid = $ulid;
    }

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
            ->with('institutions')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%");

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
            ->latest()
            ->paginate(10);
    }

    /**
     * @return array<string, int>
     */
    #[Computed]
    public function stats(): array
    {
        return [
            'total' => Challenge::count(),
            'published' => Challenge::where('status', 'published')->count(),
            'draft' => Challenge::where('status', 'draft')->count(),
            'archived' => Challenge::where('status', 'archived')->count(),
        ];
    }

    #[Computed]
    public function viewingChallenge(): ?Challenge
    {
        if (! $this->viewingUlid) {
            return null;
        }

        return Challenge::with(['institutions', 'creator', 'questions.options'])
            ->withCount([
                'completions',
                'completions as verified_completions_count' => fn ($query) => $query->where('status', 'verified'),
            ])
            ->where('ulid', $this->viewingUlid)
            ->first();
    }

    public function render()
    {
        return view('livewire.challenges.index');
    }
}
