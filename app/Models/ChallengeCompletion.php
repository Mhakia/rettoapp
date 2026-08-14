<?php

namespace App\Models;

use App\Events\ChallengeCompletionVerified;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChallengeCompletion extends Model
{
    use HasFactory;

    protected $fillable = [
        'challenge_id',
        'institution_membership_id',
        'user_id',
        'status',
        'evidence_path',
        'points_earned',
        'submitted_at',
        'verified_by',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (ChallengeCompletion $completion) {
            if ($completion->wasChanged('status') && $completion->status === 'verified') {
                ChallengeCompletionVerified::dispatch($completion);
            }
        });
    }

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(InstitutionMembership::class, 'institution_membership_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
