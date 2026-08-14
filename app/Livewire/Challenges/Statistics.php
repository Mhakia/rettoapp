<?php

namespace App\Livewire\Challenges;

use App\Models\Challenge;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Estadísticas de retos')]
class Statistics extends Component
{
    public function mount(): void
    {
        $this->authorize('viewStatistics', Challenge::class);
    }

    #[Computed]
    public function byChallenge()
    {
        return Challenge::withCount([
            'completions as submitted_count' => fn ($query) => $query->where('status', 'submitted'),
            'completions as verified_count' => fn ($query) => $query->where('status', 'verified'),
            'completions as rejected_count' => fn ($query) => $query->where('status', 'rejected'),
        ])->orderByDesc('verified_count')->get();
    }

    #[Computed]
    public function byInstitution()
    {
        return DB::table('challenge_completions')
            ->join('institution_memberships', 'institution_memberships.id', '=', 'challenge_completions.institution_membership_id')
            ->join('institutions', 'institutions.id', '=', 'institution_memberships.institution_id')
            ->select('institutions.name as institution', 'challenge_completions.status', DB::raw('count(*) as total'))
            ->groupBy('institutions.name', 'challenge_completions.status')
            ->get()
            ->groupBy('institution');
    }

    #[Computed]
    public function byTargetRole()
    {
        return DB::table('challenge_completions')
            ->join('challenges', 'challenges.id', '=', 'challenge_completions.challenge_id')
            ->select('challenges.target_role', 'challenge_completions.status', DB::raw('count(*) as total'))
            ->groupBy('challenges.target_role', 'challenge_completions.status')
            ->get()
            ->groupBy('target_role');
    }

    public function render()
    {
        return view('livewire.challenges.statistics');
    }
}
