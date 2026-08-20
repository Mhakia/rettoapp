<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Cashier\Billable;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Institution extends Model
{
    use Billable, HasFactory, HasUuids, LogsActivity, SoftDeletes;

    /**
     * Document types accepted for the institution's contact and principal.
     *
     * @var array<int, string>
     */
    public const DOCUMENT_TYPES = ['cedula_ciudadania', 'cedula_extranjeria', 'pasaporte'];

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
        'name',
        'nit',
        'address',
        'phone',
        'bulletin_frequency',
        'contact_first_name',
        'contact_middle_name',
        'contact_last_name',
        'contact_second_last_name',
        'contact_document_type',
        'contact_document_number',
        'contact_email',
        'contact_phone',
        'principal_name',
        'principal_document_type',
        'principal_document_number',
        'principal_started_at',
        'country',
        'state',
        'city',
        'entity_type',
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
            'principal_started_at' => 'date',
        ];
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(InstitutionMembership::class);
    }

    public function challenges(): BelongsToMany
    {
        return $this->belongsToMany(Challenge::class, 'challenge_institutions');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function admin(): HasOne
    {
        return $this->hasOne(User::class)->role('institution_admin');
    }
}
