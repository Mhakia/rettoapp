<?php

namespace App\Livewire\ClassSessions;

use App\Models\Challenge;
use App\Models\ChallengeCompletion;
use App\Models\ClassSession;
use App\Models\Group;
use App\Models\InstitutionMembership;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Sesiones de retos')]
class Index extends Component
{
    public string $duration = '2h';

    public ?int $viewingGroupId = null;

    public ?int $pickingGroupId = null;

    public ?int $confirmingSessionId = null;

    public ?int $resultsGroupId = null;

    public ?string $resultsChallengeUlid = null;

    public ?int $challengeId = null;

    /**
     * @return array<int, array{id: int, title: string}>
     */
    #[Computed]
    public function challengeOptions(): array
    {
        return Challenge::where('status', 'published')
            ->where('target_role', 'student')
            ->orderByDesc('starts_at')
            ->get(['id', 'title'])
            ->map(fn ($c) => ['id' => $c->id, 'title' => $c->title])
            ->all();
    }

    /**
     * @return array<int, array{id: int, name: string, students: int, session: array<string, mixed>|null, blocked: bool}>
     */
    #[Computed]
    public function groups(): array
    {
        $teacherGroupIds = Auth::user()->teacherGroups()->pluck('groups.id');

        // A teacher can only run one class session at a time, no matter how many groups they teach.
        $activeSessions = ClassSession::whereIn('group_id', $teacherGroupIds)->active()->get()->keyBy('group_id');

        return Auth::user()->teacherGroups()
            ->withCount(['memberships as active_students_count' => fn ($query) => $query->where('status', 'active')
                ->whereHas('user', fn ($q) => $q->role('student'))])
            ->orderBy('name')
            ->get()
            ->map(function (Group $group) use ($activeSessions) {
                $session = $activeSessions->get($group->id);

                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'students' => $group->active_students_count,
                    'session' => $session ? [
                        'id' => $session->id,
                        'code' => $session->code,
                        'expires_at' => $session->expires_at->translatedFormat('d M, H:i'),
                    ] : null,
                    'blocked' => ! $session && $activeSessions->isNotEmpty(),
                ];
            })
            ->all();
    }

    public function startPicking(int $groupId): void
    {
        abort_unless(Auth::user()->teacherGroups()->where('groups.id', $groupId)->exists(), 403);

        $this->pickingGroupId = $groupId;
        $this->duration = '2h';
    }

    public function cancelPicking(): void
    {
        $this->pickingGroupId = null;
    }

    public function startSession(int $groupId): void
    {
        $group = Group::findOrFail($groupId);

        $this->authorize('create', [ClassSession::class, $group]);

        $this->validate([
            'duration' => ['required', 'in:2h,today,3d'],
            'challengeId' => ['required', 'integer', 'exists:challenges,id'],
        ]);

        $teacherGroupIds = Auth::user()->teacherGroups()->pluck('groups.id');

        $hasActiveElsewhere = ClassSession::whereIn('group_id', $teacherGroupIds)
            ->where('group_id', '!=', $groupId)
            ->active()
            ->exists();

        if ($hasActiveElsewhere) {
            $this->pickingGroupId = null;

            Flux::toast(variant: 'danger', text: __('Ya tienes una sesión de retos activa en otro grupo. Ciérrala antes de iniciar una nueva.'));

            return;
        }

        $expiresAt = match ($this->duration) {
            '2h' => now()->addHours(2),
            'today' => now()->endOfDay(),
            '3d' => now()->addDays(3),
        };

        ClassSession::create([
            'group_id' => $group->id,
            'challenge_id' => $this->challengeId,
            'created_by' => Auth::id(),
            'code' => ClassSession::generateCode(),
            'expires_at' => $expiresAt,
        ]);

        unset($this->groups);

        $this->pickingGroupId = null;
        $this->viewingGroupId = $groupId;

        Flux::toast(variant: 'success', text: __('Sesión de retos iniciada.'));
    }

    public function viewCode(int $groupId): void
    {
        abort_unless(Auth::user()->teacherGroups()->where('groups.id', $groupId)->exists(), 403);

        $this->viewingGroupId = $groupId;
    }

    public function backToList(): void
    {
        $this->viewingGroupId = null;
    }

    public function cancelClose(): void
    {
        $this->confirmingSessionId = null;
    }

    public function confirmClose(): void
    {
        if ($this->confirmingSessionId) {
            $this->closeSession($this->confirmingSessionId);
        }

        $this->confirmingSessionId = null;

        $this->dispatch('modal-close', name: 'confirm-close-session');
    }

    public function closeSession(int $classSessionId): void
    {
        $session = ClassSession::findOrFail($classSessionId);

        $this->authorize('update', $session);

        if ((int) $this->viewingGroupId === (int) $session->group_id) {
            $this->viewingGroupId = null;
        }

        $session->close();

        unset($this->groups);

        Flux::toast(variant: 'success', text: __('Sesión cerrada.'));
    }

    public function viewResults(int $groupId): void
    {
        abort_unless(Auth::user()->teacherGroups()->where('groups.id', $groupId)->exists(), 403);

        $this->resultsGroupId = $groupId;
        $this->resultsChallengeUlid = null;
    }

    public function backToListFromResults(): void
    {
        $this->resultsGroupId = null;
        $this->resultsChallengeUlid = null;
    }

    public function toggleChallengeDetail(string $ulid): void
    {
        $this->resultsChallengeUlid = $this->resultsChallengeUlid === $ulid ? null : $ulid;
    }

    /**
     * Per-challenge breakdown (completed/pending/rejected/not started) for the active students
     * of a group, with a per-student row showing status, where they answered from, and how long it took.
     *
     * @return array<int, array<string, mixed>>
     */
    public function resultsFor(int $groupId): array
    {
        $group = Group::findOrFail($groupId);

        $students = InstitutionMembership::where('group_id', $groupId)
            ->where('status', 'active')
            ->whereHas('user', fn ($query) => $query->role('student'))
            ->with('user:id,name')
            ->get()
            ->pluck('user')
            ->keyBy('id');

        $challenges = Challenge::where('status', 'published')
            ->where('target_role', 'student')
            ->where(function ($query) use ($group) {
                $query->whereDoesntHave('institutions')
                    ->orWhereHas('institutions', fn ($i) => $i->where('institutions.id', $group->institution_id));
            })
            ->orderByDesc('starts_at')
            ->get();

        return $challenges->map(function (Challenge $challenge) use ($students) {
            $completions = ChallengeCompletion::where('challenge_id', $challenge->id)
                ->whereIn('user_id', $students->keys())
                ->get()
                ->keyBy('user_id');

            $rows = $students->map(function ($student) use ($completions) {
                $completion = $completions->get($student->id);

                $duration = null;

                if ($completion?->started_at && $completion->submitted_at) {
                    $minutes = $completion->started_at->diffInMinutes($completion->submitted_at);
                    $duration = $minutes < 60
                        ? __(':minutes min', ['minutes' => $minutes])
                        : __(':hours h :minutes min', ['hours' => intdiv($minutes, 60), 'minutes' => $minutes % 60]);
                }

                return [
                    'name' => $student->name,
                    'status' => $completion->status ?? 'not_started',
                    'origin' => $completion->origin ?? null,
                    'duration' => $duration,
                ];
            })->values()->all();

            return [
                'ulid' => $challenge->ulid,
                'code' => $challenge->code,
                'title' => $challenge->title,
                'total_students' => count($rows),
                'verified_count' => collect($rows)->where('status', 'verified')->count(),
                'submitted_count' => collect($rows)->where('status', 'submitted')->count(),
                'rejected_count' => collect($rows)->where('status', 'rejected')->count(),
                'not_started_count' => collect($rows)->whereIn('status', ['pending', 'not_started'])->count(),
                'rows' => $rows,
            ];
        })->all();
    }

    public function render()
    {
        return view('livewire.class-sessions.index');
    }
}
