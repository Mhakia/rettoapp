<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Challenge extends Model
{
    use HasFactory, HasUlids;

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
