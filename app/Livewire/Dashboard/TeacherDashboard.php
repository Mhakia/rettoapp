<?php

namespace App\Livewire\Dashboard;

use App\Models\ChallengeCompletion;
use App\Models\Group;
use App\Models\InstitutionMembership;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Dashboard')]
class TeacherDashboard extends Component
{
    #[Computed]
    public function groupIds()
    {
        return Auth::user()->teacherGroups()->pluck('groups.id');
    }

    #[Computed]
    public function stats(): array
    {
        $groupIds = $this->groupIds;

        $activeStudents = InstitutionMembership::where('status', 'active')
            ->whereIn('group_id', $groupIds)
            ->whereHas('user', fn ($q) => $q->role('student'))
            ->count();

        $pendingToVerify = ChallengeCompletion::where('status', 'submitted')
            ->whereHas('challenge', fn ($q) => $q->where('target_role', 'student'))
            ->whereHas('membership', fn ($q) => $q->whereIn('group_id', $groupIds))
            ->count();

        $verifiedCount = ChallengeCompletion::where('status', 'verified')
            ->whereHas('membership', fn ($q) => $q->whereIn('group_id', $groupIds))
            ->count();

        return [
            'groups_count' => $groupIds->count(),
            'active_students' => $activeStudents,
            'pending_to_verify' => $pendingToVerify,
            'verified_count' => $verifiedCount,
        ];
    }

    /**
     * @return array<int, array{name: string, students: int, verified: int, pending: int}>
     */
    #[Computed]
    public function groupBreakdown(): array
    {
        return Group::whereIn('id', $this->groupIds)
            ->withCount(['memberships as active_count' => fn ($q) => $q->where('status', 'active')])
            ->orderByDesc('active_count')
            ->get()
            ->map(fn (Group $group) => [
                'name' => $group->name,
                'students' => $group->active_count,
                'verified' => ChallengeCompletion::where('status', 'verified')
                    ->whereHas('membership', fn ($q) => $q->where('group_id', $group->id))
                    ->count(),
                'pending' => ChallengeCompletion::where('status', 'submitted')
                    ->whereHas('membership', fn ($q) => $q->where('group_id', $group->id))
                    ->count(),
            ])
            ->all();
    }

    #[Computed]
    public function recentPending()
    {
        return ChallengeCompletion::with(['challenge', 'user'])
            ->where('status', 'submitted')
            ->whereHas('challenge', fn ($q) => $q->where('target_role', 'student'))
            ->whereHas('membership', fn ($q) => $q->whereIn('group_id', $this->groupIds))
            ->latest('submitted_at')
            ->limit(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard.teacher-dashboard');
    }
}
