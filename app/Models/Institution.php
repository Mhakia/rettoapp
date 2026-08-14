<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Cashier\Billable;

class Institution extends Model
{
    use Billable, HasFactory, HasUuids;

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
    ];

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
