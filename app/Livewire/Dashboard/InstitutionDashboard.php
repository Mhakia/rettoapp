<?php

namespace App\Livewire\Dashboard;

use App\Models\Alert;
use App\Models\ChallengeCompletion;
use App\Models\Group;
use App\Models\InstitutionMembership;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Component;

class InstitutionDashboard extends Component
{
    public function mount(): void
    {
        abort_unless(Auth::user()->hasRole('institution_admin') && Auth::user()->institution_id, 403);
    }

    #[Computed]
    public function institution()
    {
        return Auth::user()->institution;
    }

    #[Computed]
    public function stats(): array
    {
        $institutionId = $this->institution->id;

        return Cache::remember("dashboard-stats-institution-{$institutionId}", 60, function () use ($institutionId) {
            $activeStudents = InstitutionMembership::where('institution_id', $institutionId)
                ->where('status', 'active')
                ->whereHas('user', fn ($q) => $q->role('student'))
                ->count();

            $activeTeachers = InstitutionMembership::where('institution_id', $institutionId)
                ->where('status', 'active')
                ->whereHas('user', fn ($q) => $q->role('teacher'))
                ->count();

            $groupsCount = Group::where('institution_id', $institutionId)->count();

            $openAlerts = Alert::whereHas('membership', fn ($q) => $q->where('institution_id', $institutionId))
                ->where('status', 'open')
                ->count();

            $alertsBySeverity = Alert::whereHas('membership', fn ($q) => $q->where('institution_id', $institutionId))
                ->where('status', 'open')
                ->selectRaw('severity, count(*) as total')
                ->groupBy('severity')
                ->pluck('total', 'severity');

            $completionsRaw = ChallengeCompletion::whereHas('membership', fn ($q) => $q->where('institution_id', $institutionId))
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            $totalPoints = ChallengeCompletion::whereHas('membership', fn ($q) => $q->where('institution_id', $institutionId))
                ->where('status', 'verified')
                ->sum('points_earned');

            return [
                'active_students' => $activeStudents,
                'active_teachers' => $activeTeachers,
                'groups_count' => $groupsCount,
                'open_alerts' => $openAlerts,
                'alerts_by_severity' => [
                    'high' => (int) ($alertsBySeverity['high'] ?? 0),
                    'medium' => (int) ($alertsBySeverity['medium'] ?? 0),
                    'low' => (int) ($alertsBySeverity['low'] ?? 0),
                ],
                'completions' => [
                    'pending' => (int) ($completionsRaw['pending'] ?? 0),
                    'submitted' => (int) ($completionsRaw['submitted'] ?? 0),
                    'verified' => (int) ($completionsRaw['verified'] ?? 0),
                    'rejected' => (int) ($completionsRaw['rejected'] ?? 0),
                ],
                'total_points' => (int) $totalPoints,
            ];
        });
    }

    #[Computed]
    public function groupBreakdown(): array
    {
        $institutionId = $this->institution->id;

        return Group::where('institution_id', $institutionId)
            ->withCount(['memberships as active_count' => fn ($q) => $q->where('status', 'active')])
            ->orderByDesc('active_count')
            ->limit(8)
            ->get()
            ->map(fn ($group) => [
                'name' => $group->name,
                'count' => $group->active_count,
            ])
            ->all();
    }

    #[Computed]
    public function enrollmentTrend(): array
    {
        $institutionId = $this->institution->id;

        $raw = InstitutionMembership::where('institution_id', $institutionId)
            ->where('started_at', '>=', now()->subMonths(5)->startOfMonth())
            ->selectRaw("to_char(started_at, 'YYYY-MM') as ym, count(*) as total")
            ->groupBy('ym')
            ->pluck('total', 'ym');

        return collect(range(5, 0))
            ->map(function ($i) use ($raw) {
                $date = now()->subMonths($i);
                $key = $date->format('Y-m');

                return [
                    'label' => mb_convert_case($date->translatedFormat('M'), MB_CASE_TITLE),
                    'count' => (int) ($raw[$key] ?? 0),
                ];
            })
            ->all();
    }

    #[Computed]
    public function topStudents(): array
    {
        $institutionId = $this->institution->id;

        return ChallengeCompletion::whereHas('membership', fn ($q) => $q->where('institution_id', $institutionId))
            ->where('status', 'verified')
            ->selectRaw('user_id, sum(points_earned) as total_points')
            ->groupBy('user_id')
            ->orderByDesc('total_points')
            ->limit(5)
            ->with('user:id,name')
            ->get()
            ->map(fn ($row) => [
                'name' => $row->user->name,
                'points' => (int) $row->total_points,
            ])
            ->all();
    }

    #[Computed]
    public function recentAlerts()
    {
        $institutionId = $this->institution->id;

        return Alert::whereHas('membership', fn ($q) => $q->where('institution_id', $institutionId))
            ->where('status', 'open')
            ->with('student.user:id,name')
            ->latest()
            ->limit(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard.institution-dashboard');
    }
}
