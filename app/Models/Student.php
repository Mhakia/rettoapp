<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    use HasFactory, HasUuids;

    /**
     * Only the uuid column is generated; id stays the auto-incrementing primary key.
     *
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    /**
     * Bind by uuid in routes instead of the auto-incrementing id.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected $fillable = [
        'user_id',
        'document_type',
        'document_number',
        'birth_date',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(InstitutionMembership::class, 'user_id', 'user_id');
    }

    public function activeMembership(): HasOne
    {
        return $this->hasOne(InstitutionMembership::class, 'user_id', 'user_id')->where('status', 'active');
    }

    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'guardian_student');
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class);
    }

    public function wellbeingIndicators(): HasMany
    {
        return $this->hasMany(WellbeingIndicator::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    public function supportPlans(): HasMany
    {
        return $this->hasMany(IndividualSupportPlan::class);
    }

    /**
     * Students visible to a teacher: same group as one of their active teacher_group assignments.
     */
    public function scopeForTeacher(Builder $query, User $teacher): Builder
    {
        $groupIds = $teacher->teacherGroups()->pluck('groups.id');

        return $query->whereHas('activeMembership', function (Builder $membership) use ($groupIds) {
            $membership->whereIn('group_id', $groupIds);
        });
    }
}
