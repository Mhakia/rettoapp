<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class InstitutionSubscription extends Model
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
        'institution_id',
        'plan_id',
        'contract_id',
        'base_price',
        'included_students',
        'price_per_extra_student',
        'discount_type',
        'discount_value',
        'billing_cycle',
        'features',
        'status',
        'started_at',
        'ended_at',
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
            'base_price' => 'decimal:2',
            'price_per_extra_student' => 'decimal:2',
            'discount_value' => 'decimal:2',
            'features' => 'array',
            'started_at' => 'date',
            'ended_at' => 'date',
        ];
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * The plan this subscription started from. Informational only — nothing here
     * is read back from the plan at billing time, so changing the plan's catalog
     * price later never affects institutions already subscribed.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function pricingTiers(): HasMany
    {
        return $this->hasMany(SubscriptionPricingTier::class);
    }

    public function addons(): HasMany
    {
        return $this->hasMany(SubscriptionAddon::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * The [start, end] dates of the next period to invoice, based on the last
     * invoice issued (or `started_at` if this subscription has never been billed)
     * and its billing cycle.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    public function nextPeriod(): array
    {
        $lastInvoice = $this->invoices()->latest('period_end')->first();

        $start = $lastInvoice
            ? $lastInvoice->period_end->copy()->addDay()
            : $this->started_at->copy();

        $end = match ($this->billing_cycle) {
            'quarterly' => $start->copy()->addMonths(3)->subDay(),
            'annual' => $start->copy()->addYear()->subDay(),
            default => $start->copy()->addMonth()->subDay(),
        };

        return [$start, $end];
    }

    /**
     * Whether the next period is ready to be invoiced (its start date has arrived).
     */
    public function isDueForInvoicing(): bool
    {
        [$start] = $this->nextPeriod();

        return $start->lessThanOrEqualTo(now());
    }

    /**
     * Full billing amount for one cycle, given the institution's current billable
     * student count: base price + cost of students beyond `included_students`
     * (using pricing tiers when configured, otherwise the flat per-extra-student
     * price), plus active addons, minus any discount.
     */
    public function calculateAmount(int $studentCount): float
    {
        $extraStudents = max(0, $studentCount - $this->included_students);

        $extraAmount = $this->pricingTiers()->exists()
            ? $this->calculateTieredAmount($extraStudents)
            : $extraStudents * (float) $this->price_per_extra_student;

        $subtotal = (float) $this->base_price + $extraAmount + $this->addonsTotal();

        return $this->applyDiscount($subtotal);
    }

    /**
     * Sum of extra-student cost across configured volume tiers, cheapest tier first.
     */
    protected function calculateTieredAmount(int $extraStudents): float
    {
        $remaining = $extraStudents;
        $amount = 0.0;

        foreach ($this->pricingTiers()->orderBy('min_students')->get() as $tier) {
            if ($remaining <= 0) {
                break;
            }

            $tierCapacity = $tier->max_students
                ? ($tier->max_students - $tier->min_students + 1)
                : $remaining;

            $studentsInTier = min($remaining, $tierCapacity);
            $amount += $studentsInTier * (float) $tier->price_per_student;
            $remaining -= $studentsInTier;
        }

        return $amount;
    }

    /**
     * Recurring addons only; one-time addons (billing_cycle null) are invoiced
     * separately and not part of the recurring amount.
     */
    protected function addonsTotal(): float
    {
        return (float) $this->addons()->whereNotNull('billing_cycle')->sum('price');
    }

    protected function applyDiscount(float $amount): float
    {
        return match ($this->discount_type) {
            'percentage' => $amount * (1 - (float) $this->discount_value / 100),
            'fixed' => max(0, $amount - (float) $this->discount_value),
            default => $amount,
        };
    }

    /**
     * Ensure only one active subscription per institution.
     * The unique index enforces this at the database level, but this validation
     * catches it earlier for better error messages.
     */
    protected static function booted(): void
    {
        static::saving(function (self $subscription) {
            if ($subscription->status === 'active') {
                $existingActive = self::query()
                    ->where('institution_id', $subscription->institution_id)
                    ->where('status', 'active')
                    ->where('id', '!=', $subscription->id ?? 0)
                    ->exists();

                if ($existingActive) {
                    throw new ValidationException(
                        Validator::make([], [])->errors()->add(
                            'status',
                            __('Esta institución ya tiene una suscripción activa. Finaliza la actual antes de crear una nueva.'),
                        )
                    );
                }
            }
        });
    }
}
