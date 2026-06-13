<?php

declare(strict_types=1);

namespace IvanBaric\Plans\Services;

use Illuminate\Database\Eloquent\Model;
use IvanBaric\Plans\Contracts\CurrentPlanResolver;

final class DefaultCurrentPlanResolver implements CurrentPlanResolver
{
    public function resolveForBillable(Model $billable): ?string
    {
        $column = (string) config('plans.billing.plan_column', 'plan_code');
        $value = $billable->getAttribute($column);

        if (! is_string($value) || $value === '') {
            $defaultPlan = config('plans.default_plan');
            $value = is_string($defaultPlan) ? $defaultPlan : null;
        }

        return is_string($value) && $value !== '' ? $value : null;
    }
}
