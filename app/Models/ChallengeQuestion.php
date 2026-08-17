<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class ChallengeQuestion extends Model
{
    protected $fillable = [
        'challenge_id',
        'title',
        'description',
        'points',
        'answer_type',
        'answer_mode',
        'min_selections',
        'is_scored',
        'auto_verify',
    ];

    protected function casts(): array
    {
        return [
            'is_scored' => 'boolean',
            'auto_verify' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (ChallengeQuestion $question) {
            if ($question->answer_type === 'choice') {
                if (! in_array($question->answer_mode, ['single', 'multiple'], true)) {
                    throw ValidationException::withMessages([
                        'answer_mode' => 'Una pregunta de opción debe ser de tipo "single" o "multiple".',
                    ]);
                }

                if ($question->answer_mode === 'multiple' && ($question->min_selections < 1 || $question->min_selections > 3)) {
                    throw ValidationException::withMessages([
                        'min_selections' => 'El mínimo de selecciones obligatorias debe estar entre 1 y 3.',
                    ]);
                }
            }

            $cap = $question->challenge->remainingPoints($question->id);

            if ($question->points > $cap) {
                throw ValidationException::withMessages([
                    'points' => "La suma de puntos de las preguntas no puede superar los {$question->challenge->points} puntos del reto.",
                ]);
            }
        });
    }

    /**
     * Short reference code shown to users, e.g. "P-0007".
     */
    public function getCodeAttribute(): string
    {
        return sprintf('P-%04d', $this->id);
    }

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(ChallengeQuestionOption::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ChallengeQuestionAnswer::class);
    }

    /**
     * How many options the user may pick at most: 1 for single choice, 3 for multiple choice.
     */
    public function maxSelections(): int
    {
        return $this->answer_mode === 'multiple' ? 3 : 1;
    }

    /**
     * A selection is correct when it contains only correct options and meets the
     * configured minimum (single mode always requires exactly the one correct option).
     *
     * @param  array<int, int>  $selectedOptionIds
     */
    public function isSelectionCorrect(array $selectedOptionIds): bool
    {
        if ($this->answer_type !== 'choice' || ! $this->is_scored) {
            return false;
        }

        $correctIds = $this->options()->where('is_correct', true)->pluck('id')->all();

        if (empty($selectedOptionIds) || array_diff($selectedOptionIds, $correctIds) !== []) {
            return false;
        }

        if ($this->answer_mode === 'single') {
            return count($selectedOptionIds) === 1;
        }

        return count($selectedOptionIds) >= ($this->min_selections ?? 1);
    }
}
