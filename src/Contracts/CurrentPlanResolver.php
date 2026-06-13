<?php

declare(strict_types=1);

namespace IvanBaric\Plans\Contracts;

use Illuminate\Database\Eloquent\Model;

interface CurrentPlanResolver
{
    public function resolveForBillable(Model $billable): ?string;
}
