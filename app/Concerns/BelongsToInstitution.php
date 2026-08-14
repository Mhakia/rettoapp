<?php

namespace App\Concerns;

use App\Models\Institution;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * For models with a direct institution_id column (institution_admin's own scope).
 * Not for portable-identity models (student/teacher), which scope via institution_membership_id instead.
 */
trait BelongsToInstitution
{
    protected static function bootBelongsToInstitution(): void
    {
        static::addGlobalScope('institution', function (Builder $builder) {
            $user = Auth::user();

            if ($user && $user->institution_id) {
                $builder->where($builder->getModel()->getTable().'.institution_id', $user->institution_id);
            }
        });
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }
}
