<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionPricingTier extends Model
{
    protected $fillable = [
        'institution_subscription_id',
        'min_students',
        'max_students',
        'price_per_student',
    ];

    protected function casts(): array
    {
        return [
            'price_per_student' => 'decimal:2',
        ];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(InstitutionSubscription::class, 'institution_subscription_id');
    }
}
