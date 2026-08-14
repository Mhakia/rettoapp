<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportPlanVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'individual_support_plan_id',
        'content',
        'created_by',
    ];

    public function supportPlan(): BelongsTo
    {
        return $this->belongsTo(IndividualSupportPlan::class, 'individual_support_plan_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
