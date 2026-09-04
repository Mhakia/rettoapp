<?php

namespace App\Livewire\ClassSessions;

use App\Models\ClassSession;
use App\Models\InstitutionMembership;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.auth')]
class Join extends Component
{
    public string $code = '';

    public ?int $activeSessionId = null;

    public ?string $groupName = null;

    /**
     * @return array<int, array{uuid: string, name: string, initials: string}>
     */
    #[Computed]
    public function students(): array
    {
        $session = $this->activeSessionId ? ClassSession::active()->find($this->activeSessionId) : null;

        if (! $session) {
            return [];
        }

        return InstitutionMembership::where('group_id', $session->group_id)
            ->where('status', 'active')
            ->whereHas('user', fn ($query) => $query->role('student'))
            ->with('user:id,uuid,name')
            ->get()
            ->map(fn (InstitutionMembership $membership) => [
                'uuid' => $membership->user->uuid,
                'name' => $membership->user->name,
                'initials' => $membership->user->initials(),
            ])
            ->all();
    }

    public function submitCode(): void
    {
        $this->validate(['code' => ['required', 'string', 'max:10']]);

        $session = ClassSession::active()
            ->where('code', Str::upper(trim($this->code)))
            ->with('group')
            ->first();

        if (! $session) {
            $this->addError('code', __('Invalid or expired code.'));

            return;
        }

        $this->activeSessionId = $session->id;
        $this->groupName = $session->group->name;
    }

    public function backToCode(): void
    {
        $this->reset(['code', 'activeSessionId', 'groupName']);
        unset($this->students);
    }

    public function selectStudent(string $userUuid): void
    {
        // Re-validate on click: the code may have expired while the roster was on screen.
        $session = $this->activeSessionId ? ClassSession::active()->with('challenge')->find($this->activeSessionId) : null;

        abort_unless($session, 410, __('This challenge session is no longer active.'));

        $membership = InstitutionMembership::where('group_id', $session->group_id)
            ->where('status', 'active')
            ->whereHas('user', fn ($query) => $query->where('uuid', $userUuid)->role('student'))
            ->with('user')
            ->first();

        abort_unless($membership, 404);

        Auth::guard('web')->logout();
        Session::invalidate();
        Session::regenerateToken();

        Auth::login($membership->user);
        Session::regenerate();
        Session::put('challenge_origin', 'class_session');
        Session::put('student_access_mode', true);
        Session::put('locked_challenge_ulid', $session->challenge?->ulid);

        $this->redirect(route('challenges.catalog'), navigate: true);
    }

    public function render()
    {
        return view('livewire.class-sessions.join')->title(__('go_to_my_challenges'));
    }
}
