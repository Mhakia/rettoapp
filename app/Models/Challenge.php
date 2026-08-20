<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Challenge extends Model
{
    use HasFactory, HasUlids, LogsActivity, SoftDeletes;

    /**
     * Default points cap when a challenge is created without specifying one.
     */
    protected $attributes = [
        'points' => 100,
    ];

    /**
     * Only the ulid column is generated; id stays the auto-incrementing primary key.
     *
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    /**
     * Bind by ulid in routes instead of the auto-incrementing id.
     */
    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    /**
     * Short reference code shown to users, e.g. "R-0007" (questions use the "P-" prefix instead).
     */
    public function getCodeAttribute(): string
    {
        return sprintf('R-%04d', $this->id);
    }

    protected $fillable = [
        'target_role',
        'title',
        'description',
        'category',
        'points',
        'difficulty',
        'starts_at',
        'ends_at',
        'status',
        'created_by',
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
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function institutions(): BelongsToMany
    {
        return $this->belongsToMany(Institution::class, 'challenge_institutions');
    }

    public function completions(): HasMany
    {
        return $this->hasMany(ChallengeCompletion::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(ChallengeQuestion::class);
    }

    /**
     * Points still available for new/edited questions before exceeding this challenge's cap.
     */
    public function remainingPoints(?int $excludingQuestionId = null): int
    {
        $used = $this->questions()
            ->when($excludingQuestionId, fn ($query) => $query->where('id', '!=', $excludingQuestionId))
            ->sum('points');

        return max(0, $this->points - $used);
    }

    /**
     * Published challenges targeting the user's role and visible to their current institution(s)
     * (no challenge_institutions rows means visible to every institution).
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        $role = $user->getRoleNames()->first();
        $institutionIds = $user->currentInstitutionIds();

        return $query->where('status', 'published')
            ->where('target_role', $role)
            ->where(function (Builder $q) use ($institutionIds) {
                $q->whereDoesntHave('institutions');

                if (! empty($institutionIds)) {
                    $q->orWhereHas('institutions', fn (Builder $i) => $i->whereIn('institutions.id', $institutionIds));
                }
            });
    }
}
