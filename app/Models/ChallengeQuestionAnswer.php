<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class ChallengeQuestionAnswer extends Model
{
    use LogsActivity;

    protected $fillable = [
        'challenge_completion_id',
        'challenge_question_id',
        'status',
        'evidence_path',
        'points_earned',
        'submitted_at',
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
            'submitted_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (ChallengeQuestionAnswer $answer) {
            $answer->completion->recalculateStatus();
        });
    }

    public function completion(): BelongsTo
    {
        return $this->belongsTo(ChallengeCompletion::class, 'challenge_completion_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(ChallengeQuestion::class, 'challenge_question_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function selectedOptions(): BelongsToMany
    {
        return $this->belongsToMany(
            ChallengeQuestionOption::class,
            'challenge_question_answer_selections'
        );
    }

    /**
     * Record a user's answer to a question: selected options for choice questions,
     * an uploaded file path for evidence questions. Auto-verifies choice questions
     * when the question allows it.
     *
     * @param  array<int, int>  $optionIds
     */
    public static function record(ChallengeCompletion $completion, ChallengeQuestion $question, array $optionIds = [], ?string $evidencePath = null): self
    {
        if ($question->answer_type === 'choice' && count($optionIds) > $question->maxSelections()) {
            throw ValidationException::withMessages([
                'options' => "Solo puedes elegir hasta {$question->maxSelections()} opción(es) para esta pregunta.",
            ]);
        }

        return DB::transaction(function () use ($completion, $question, $optionIds, $evidencePath) {
            $answer = static::firstOrNew([
                'challenge_completion_id' => $completion->id,
                'challenge_question_id' => $question->id,
            ]);
            $answer->submitted_at = now();

            if ($question->answer_type === 'choice') {
                $answer->save();
                $answer->selectedOptions()->sync($optionIds);

                if ($question->auto_verify) {
                    $correct = $question->is_scored ? $question->isSelectionCorrect($optionIds) : true;

                    $answer->fill([
                        'status' => $question->is_scored && ! $correct ? 'rejected' : 'verified',
                        'points_earned' => $correct && $question->is_scored ? $question->points : 0,
                        'verified_at' => now(),
                    ]);
                } else {
                    $answer->status = 'submitted';
                }
            } else {
                $answer->fill([
                    'evidence_path' => $evidencePath,
                    'status' => 'submitted',
                ]);
            }

            $answer->save();

            return $answer;
        });
    }

    /**
     * Manually verify/reject an answer that required review (evidence, or choice with auto_verify off).
     */
    public function decide(string $status, User $verifier): void
    {
        $this->update([
            'status' => $status,
            'points_earned' => $status === 'verified' ? ($this->question->is_scored ? $this->question->points : 0) : null,
            'verified_by' => $verifier->id,
            'verified_at' => now(),
        ]);
    }
}
