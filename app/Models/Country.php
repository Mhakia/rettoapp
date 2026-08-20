<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Nnjeim\World\Models\Country as BaseCountry;

class Country extends BaseCountry
{
    public function scopeLatam(Builder $query): Builder
    {
        return $query->whereIn('iso2', config('latam.allowed_country_codes'));
    }

    /**
     * Orders alphabetically, optionally with a fixed country first.
     *
     * @param  string  $direction  'asc' | 'desc'
     * @param  array  $priorityOrder  iso2 of the country(ies) that should always come first (optional)
     */
    public function scopeOrdered(Builder $query, string $direction = 'asc', array $priorityOrder = []): Builder
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        if (! empty($priorityOrder)) {
            $cases = collect($priorityOrder)
                ->map(fn ($iso2, $i) => "WHEN ? THEN {$i}")
                ->implode(' ');

            $fallback = count($priorityOrder);

            $query->orderByRaw(
                "CASE iso2 {$cases} ELSE {$fallback} END",
                $priorityOrder
            );
        }

        return $query->orderBy('name', $direction);
    }
}
