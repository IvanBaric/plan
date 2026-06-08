<?php

declare(strict_types=1);

namespace IvanBaric\Plans\Actions;

use IvanBaric\Plans\Services\PlanDefinitionSynchronizer;

final readonly class SyncPlansFromConfigAction
{
    public function __construct(
        private PlanDefinitionSynchronizer $synchronizer,
    ) {}

    public function handle(bool $deactivateMissing = false): void
    {
        $this->synchronizer->sync($deactivateMissing);
    }
}
