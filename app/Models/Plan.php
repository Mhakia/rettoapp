<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
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
        'name',
        'slug',
        'description',
        'base_price',
        'included_students',
        'price_per_extra_student',
        'billing_cycle',
        'features',
        'is_public',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'price_per_extra_student' => 'decimal:2',
            'features' => 'array',
            'is_public' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Subscriptions that originated from this plan. Purely informational: editing a
     * plan never changes what an institution already agreed to pay (see InstitutionSubscription).
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(InstitutionSubscription::class);
    }
}
