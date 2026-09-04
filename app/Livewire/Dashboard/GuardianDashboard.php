<?php

namespace App\Livewire\Dashboard;

use App\Models\ChallengeCompletion;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Dashboard')]
class GuardianDashboard extends Component
{
    /**
     * @return array<int, array{uuid: string, name: string, institution: string, group: string, points: int, verifiedCount: int, pendingCount: int}>
     */
    #[Computed]
    public function children(): array
    {
        return Auth::user()->guardianStudents()
            ->with(['user:id,name,uuid', 'activeMembership.institution:id,name', 'activeMembership.group:id,name'])
            ->get()
            ->map(function (Student $student) {
                $membership = $student->activeMembership;

                return [
                    'uuid' => $student->uuid,
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

    public function viewChildChallenges(string $studentUuid): void
    {
        $student = Auth::user()->guardianStudents()
            ->where('students.uuid', $studentUuid)
            ->firstOrFail();

        Session::put('guardian_return_id', Auth::id());

        Auth::login($student->user);
        Session::regenerate();
        Session::put('challenge_origin', 'guardian');
        Session::put('student_access_mode', true);

        $this->redirect(route('challenges.catalog'), navigate: true);
    }

    public function render()
    {
        return view('livewire.dashboard.guardian-dashboard');
    }
}
