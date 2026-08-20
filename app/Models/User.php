<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'first_name', 'last_name', 'email', 'password', 'institution_id', 'document_type', 'document_number', 'phone', 'birth_date', 'address', 'import_batch_id'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail, PasskeyUser
{
    use HasFactory, HasRoles, HasUuids, LogsActivity, Notifiable, PasskeyAuthenticatable, SoftDeletes, TwoFactorAuthenticatable;

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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
        ];
    }

    /**
     * Only these attributes are recorded; password/2FA secrets are excluded from the audit log.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'first_name', 'last_name', 'email', 'institution_id', 'document_type', 'document_number', 'phone', 'birth_date', 'address'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Fixed institution, only set for institution_admin.
     */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * The bulk import that created this account, only set for teachers/internal users created via Excel.
     */
    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class);
    }

    /**
     * The student profile, only set when this user is a student.
     */
    public function studentProfile(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    /**
     * All institution memberships (student/teacher only), past and present.
     */
    public function institutionMemberships(): HasMany
    {
        return $this->hasMany(InstitutionMembership::class);
    }

    /**
     * The single active membership (student/teacher), if any.
     */
    public function activeMembership(): HasOne
    {
        return $this->hasOne(InstitutionMembership::class)->where('status', 'active');
    }

    /**
     * Groups assigned to this teacher.
     */
    public function teacherGroups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'teacher_group');
    }

    /**
     * Students linked to this guardian.
     */
    public function guardianStudents(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'guardian_student');
    }

    public function challengeCompletions(): HasMany
    {
        return $this->hasMany(ChallengeCompletion::class);
    }

    /**
     * Institution ids relevant to this user right now, based on their role:
     * institution_admin -> their fixed institution; student/teacher -> their active membership;
     * guardian -> the active institutions of every linked student.
     *
     * @return array<int, int>
     */
    public function currentInstitutionIds(): array
    {
        if ($this->institution_id) {
            return [$this->institution_id];
        }

        if ($this->hasRole('guardian')) {
            return $this->guardianStudents()
                ->with('activeMembership')
                ->get()
                ->pluck('activeMembership.institution_id')
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        $activeMembership = $this->activeMembership()->first();

        return $activeMembership ? [$activeMembership->institution_id] : [];
    }
}
