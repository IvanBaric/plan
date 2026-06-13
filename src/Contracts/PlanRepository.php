<?php

declare(strict_types=1);

namespace IvanBaric\Plans\Contracts;

use IvanBaric\Plans\Data\PlanDefinition;

interface PlanRepository
{
    /**
     * @return array<string, PlanDefinition>
     */
    public function all(): array;

    public function find(string $code): ?PlanDefinition;

    public function get(string $code): PlanDefinition;
}
