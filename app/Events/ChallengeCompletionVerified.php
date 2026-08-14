<?php

namespace App\Events;

use App\Models\ChallengeCompletion;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChallengeCompletionVerified implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public ChallengeCompletion $completion) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("App.Models.User.{$this->completion->user_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'challenge.verified';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'challenge_id' => $this->completion->challenge_id,
            'challenge_title' => $this->completion->challenge->title,
            'points_earned' => $this->completion->points_earned,
        ];
    }
}
