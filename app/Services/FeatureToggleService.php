<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Laravel\Pennant\Feature;
use Spatie\Permission\Models\Role;

/**
 * Never call Feature::activate()/deactivate() directly from the UI: this is the single
 * place that applies a Pennant toggle AND records who/what/when via activitylog.
 */
class FeatureToggleService
{
    public function toggle(string $featureKey, mixed $scope, bool $active, User $actor): void
    {
        $active
            ? Feature::for($scope)->activate($featureKey)
            : Feature::for($scope)->deactivate($featureKey);

        activity('features')
            ->causedBy($actor)
            ->withProperties([
                'feature' => $featureKey,
                'scope' => $this->describeScope($scope),
                'active' => $active,
            ])
            ->event($active ? 'activated' : 'deactivated')
            ->log('Feature toggled');
    }

    private function describeScope(mixed $scope): string
    {
        if ($scope === null || $scope === 'global') {
            return 'global';
        }

        if ($scope instanceof Role) {
            return "role:{$scope->name}";
        }

        if ($scope instanceof Model) {
            return sprintf('%s:%s', class_basename($scope), $scope->getKey());
        }

        return (string) $scope;
    }
}
