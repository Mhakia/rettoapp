<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Invoice extends Model
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
        'institution_subscription_id',
        'number',
        'period_start',
        'period_end',
        'billed_student_count',
        'subtotal',
        'discount_amount',
        'total',
        'currency',
        'payment_method',
        'stripe_invoice_id',
        'wompi_reference',
        'status',
        'due_at',
        'paid_at',
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
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'period_start' => 'date',
            'period_end' => 'date',
            'due_at' => 'date',
            'paid_at' => 'datetime',
        ];
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(InstitutionSubscription::class, 'institution_subscription_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Build a full invoice (header + line items) for one billing period, using the
     * subscription's current terms and the institution's live student count.
     * Wrapped in a transaction so the invoice never ends up without its items.
     */
    public static function generateFor(InstitutionSubscription $subscription, \DateTimeInterface $periodStart, \DateTimeInterface $periodEnd, ?int $dueInDays = 15): self
    {
        return DB::transaction(function () use ($subscription, $periodStart, $periodEnd, $dueInDays) {
            $studentCount = $subscription->institution->billableStudentCount();
            $extraStudents = max(0, $studentCount - $subscription->included_students);

            $invoice = static::create([
                'institution_id' => $subscription->institution_id,
                'institution_subscription_id' => $subscription->id,
                'number' => static::nextNumber(),
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'billed_student_count' => $studentCount,
                'subtotal' => 0,
                'discount_amount' => 0,
                'total' => 0,
                // Convenios (Contract) siempre se facturan manualmente, sin importar
                // qué pasarelas estén habilitadas; el resto usa lo que diga la config,
                // por defecto solo Wompi (ver config/services.php -> billing.gateways).
                'payment_method' => $subscription->contract_id
                    ? 'manual'
                    : implode(',', config('services.billing.gateways', ['wompi'])),
                'due_at' => now()->addDays($dueInDays),
            ]);

            $invoice->items()->create([
                'type' => 'base',
                'description' => 'Suscripción base',
                'quantity' => 1,
                'unit_price' => $subscription->base_price,
                'amount' => $subscription->base_price,
            ]);

            if ($extraStudents > 0) {
                $invoice->items()->create([
                    'type' => 'extra_students',
                    'description' => "Alumnos adicionales ({$extraStudents} por encima de {$subscription->included_students})",
                    'quantity' => $extraStudents,
                    'unit_price' => $subscription->price_per_extra_student,
                    'amount' => $extraStudents * (float) $subscription->price_per_extra_student,
                ]);
            }

            foreach ($subscription->addons()->whereNotNull('billing_cycle')->get() as $addon) {
                $invoice->items()->create([
                    'type' => 'addon',
                    'description' => $addon->name,
                    'quantity' => 1,
                    'unit_price' => $addon->price,
                    'amount' => $addon->price,
                ]);
            }

            $subtotal = (float) $invoice->items()->sum('amount');
            $total = $subscription->calculateAmount($studentCount);
            $discountAmount = max(0, $subtotal - $total);

            if ($discountAmount > 0) {
                $invoice->items()->create([
                    'type' => 'discount',
                    'description' => 'Descuento negociado',
                    'quantity' => 1,
                    'unit_price' => -$discountAmount,
                    'amount' => -$discountAmount,
                ]);
            }

            $invoice->update([
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'total' => $total,
            ]);

            return $invoice->fresh('items');
        });
    }

    protected static function nextNumber(): string
    {
        $year = now()->year;
        $sequence = static::whereYear('created_at', $year)->count() + 1;

        return sprintf('INV-%d-%06d', $year, $sequence);
    }

    /**
     * The gateway(s) offered for this invoice, as an array (e.g. ['wompi'],
     * ['stripe'], or ['wompi', 'stripe'] when both are enabled). 'manual'
     * (convenios) is never a real gateway, so it resolves to an empty array.
     *
     * @return array<int, string>
     */
    public function gateways(): array
    {
        if ($this->payment_method === 'manual') {
            return [];
        }

        return explode(',', $this->payment_method);
    }
}
