<?php

namespace App\Models;

use App\Events\ChallengeCompletionVerified;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class ChallengeCompletion extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'challenge_id',
        'institution_membership_id',
        'user_id',
        'status',
        'evidence_path',
        'points_earned',
        'started_at',
        'submitted_at',
        'origin',
        'verified_by',
        'verified_at',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
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

    public function questionAnswers(): HasMany
    {
        return $this->hasMany(ChallengeQuestionAnswer::class);
    }

    /**
     * Recompute this completion's overall status/points from its per-question answers:
     * rejected if any question was rejected, pending until every question has an answer,
     * submitted while a choice (manual) or evidence answer is still awaiting review,
     * verified (summing points) once every question is verified.
     */
    public function recalculateStatus(): void
    {
        $totalQuestions = $this->challenge->questions()->count();

        if ($totalQuestions === 0) {
            return;
        }

        $answers = $this->questionAnswers()->get();

        $status = match (true) {
            $answers->contains('status', 'rejected') => 'rejected',
            $answers->count() < $totalQuestions => 'pending',
            $answers->contains(fn (ChallengeQuestionAnswer $answer) => $answer->status !== 'verified') => 'submitted',
            default => 'verified',
        };

        $this->update([
            'status' => $status,
            'points_earned' => $status === 'verified' ? $answers->sum('points_earned') : null,
            'submitted_at' => $this->submitted_at ?? now(),
            'verified_at' => $status === 'verified' ? now() : null,
        ]);
    }
}
