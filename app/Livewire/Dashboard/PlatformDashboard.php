<?php

namespace App\Livewire\Dashboard;

use App\Models\Alert;
use App\Models\Challenge;
use App\Models\Institution;
use App\Models\InstitutionMembership;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Dashboard')]
class PlatformDashboard extends Component
{
    #[Computed]
    public function stats(): array
    {
        return Cache::remember('dashboard-stats-platform', 60, function () {
            return [
                'institutions_count' => Institution::count(),
                'active_students' => InstitutionMembership::where('status', 'active')
                    ->whereHas('user', fn ($q) => $q->role('student'))
                    ->count(),
                'active_teachers' => InstitutionMembership::where('status', 'active')
                    ->whereHas('user', fn ($q) => $q->role('teacher'))
                    ->count(),
                'published_challenges' => Challenge::where('status', 'published')->count(),
                'open_alerts' => Alert::where('status', 'open')->count(),
            ];
        });
    }

    /**
     * @return array<int, array{name: string, count: int}>
     */
    #[Computed]
    public function topInstitutions(): array
    {
        return Institution::withCount([
            'memberships as active_student_count' => fn ($query) => $query->where('status', 'active')
                ->whereHas('user', fn ($q) => $q->role('student')),
        ])
            ->orderByDesc('active_student_count')
            ->limit(6)
            ->get()
            ->map(fn (Institution $institution) => [
                'name' => $institution->name,
                'count' => $institution->active_student_count,
            ])
            ->all();
    }

    public function render()
    {
        return view('livewire.dashboard.platform-dashboard');
    }
}
