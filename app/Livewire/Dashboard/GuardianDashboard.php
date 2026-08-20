<?php

namespace App\Livewire\Dashboard;

use App\Models\ChallengeCompletion;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Dashboard')]
class GuardianDashboard extends Component
{
    /**
     * @return array<int, array{name: string, institution: string, group: string, points: int, verifiedCount: int, pendingCount: int}>
     */
    #[Computed]
    public function children(): array
    {
        return Auth::user()->guardianStudents()
            ->with(['user:id,name', 'activeMembership.institution:id,name', 'activeMembership.group:id,name'])
            ->get()
            ->map(function (Student $student) {
                $membership = $student->activeMembership;

                return [
                    'name' => $student->user->name,
                    'institution' => $membership?->institution?->name ?? '—',
                    'group' => $membership?->group?->name ?? '—',
                    'points' => (int) ChallengeCompletion::where('user_id', $student->user_id)
                        ->where('status', 'verified')
                        ->sum('points_earned'),
                    'verifiedCount' => ChallengeCompletion::where('user_id', $student->user_id)
                        ->where('status', 'verified')
                        ->count(),
                    'pendingCount' => ChallengeCompletion::where('user_id', $student->user_id)
                        ->where('status', 'submitted')
                        ->count(),
                ];
            })
            ->all();
    }

    public function render()
    {
        return view('livewire.dashboard.guardian-dashboard');
    }
}
