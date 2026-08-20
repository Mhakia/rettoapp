<?php

namespace App\Livewire\Alerts;

use App\Models\Alert;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Alertas')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $severity = '';

    #[Url]
    public string $status = 'open';

    public function mount(): void
    {
        abort_unless(Auth::user()->hasAnyRole(['institution_admin', 'teacher']), 403);
    }

    public function updatedSeverity(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    /**
     * Keyset (cursor) pagination stays fast no matter how many alerts exist.
     */
    #[Computed]
    public function alerts()
    {
        $user = Auth::user();

        $query = Alert::query();

        if ($user->hasRole('teacher')) {
            $groupIds = $user->teacherGroups()->pluck('groups.id');
            $query->whereHas('membership', fn ($q) => $q->whereIn('group_id', $groupIds));
        } else {
            $query->whereHas('membership', fn ($q) => $q->where('institution_id', $user->institution_id));
        }

        return $query
            ->when($this->severity !== '', fn ($q) => $q->where('severity', $this->severity))
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->with(['student.user:id,name', 'creator:id,name'])
            ->orderByDesc('id')
            ->cursorPaginate(10);
    }

    /**
     * A key that changes whenever the current page's set of alerts changes, used to force
     * Alpine to reinitialize with fresh `alertDetails` instead of keeping stale client state.
     */
    #[Computed]
    public function alertsCacheKey(): string
    {
        return md5($this->alerts->pluck('id')->implode(','));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function alertDetails(): array
    {
        return $this->alerts->getCollection()->mapWithKeys(fn (Alert $alert) => [
            $alert->id => [
                'student' => $alert->student->user->name ?? '—',
                'type' => $alert->type,
                'severity' => $alert->severity,
                'status' => $alert->status,
                'message' => $alert->message,
                'creator' => $alert->creator?->name,
                'createdAt' => $alert->created_at?->format('d/m/Y H:i'),
                'resolvedAt' => $alert->resolved_at?->format('d/m/Y H:i'),
            ],
        ])->all();
    }

    public function resolve(int $id): void
    {
        $alert = Alert::findOrFail($id);
        $this->authorize('resolve', $alert);

        $alert->update(['status' => 'resolved', 'resolved_at' => now()]);

        unset($this->alerts, $this->alertsCacheKey, $this->alertDetails);

        Flux::toast(variant: 'success', text: __('Alerta resuelta.'));
    }

    public function render()
    {
        return view('livewire.alerts.index');
    }
}
