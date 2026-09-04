<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Contract extends Model
{
    use HasFactory, HasUuids, LogsActivity, SoftDeletes;

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
        'type',
        'entity_name',
        'default_price_per_student',
        'default_included_students',
        'negotiated_by',
        'starts_at',
        'ends_at',
        'status',
        'notes',
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
            'default_price_per_student' => 'decimal:2',
            'starts_at' => 'date',
            'ends_at' => 'date',
        ];
    }

    /**
     * Subscriptions billed under this contract's negotiated terms.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(InstitutionSubscription::class);
    }
}
