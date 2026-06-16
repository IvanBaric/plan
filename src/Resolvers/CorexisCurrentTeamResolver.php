<?php

declare(strict_types=1);

namespace IvanBaric\Plans\Resolvers;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;
use IvanBaric\Corexis\Contracts\TenantResolver;
use IvanBaric\Plans\Contracts\CurrentTeamResolver;

final readonly class CorexisCurrentTeamResolver implements CurrentTeamResolver
{
    public function __construct(
        private TenantResolver $tenantResolver,
    ) {}

    public function current(): Model
    {
        $tenant = $this->tenantResolver->current();

        if ($this->tenantResolver->enabled() && $tenant instanceof Model) {
            return $tenant;
        }

        throw new RuntimeException(__('plans::messages.current_team_not_resolved'));
    }
}
