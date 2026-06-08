<?php

declare(strict_types=1);

namespace IvanBaric\Plans\Contracts;

use Illuminate\Database\Eloquent\Model;
use IvanBaric\Plans\Data\UsagePeriod;
use IvanBaric\Plans\Models\PlanKey;

interface PlanUsageResolver
{
    public function used(
        Model $owner,
        PlanKey $key,
        ?UsagePeriod $period = null,
    ): int;
}
