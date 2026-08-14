<?php

namespace App\Events;

use App\Models\Alert;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AlertRaised implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Alert $alert) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        $institutionId = $this->alert->membership?->institution_id
            ?? $this->alert->student->activeMembership?->institution_id;

        return [
            new PrivateChannel("institution.{$institutionId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'alert.raised';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'alert_id' => $this->alert->id,
            'student_id' => $this->alert->student_id,
            'severity' => $this->alert->severity,
            'message' => $this->alert->message,
        ];
    }
}
