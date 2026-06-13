<?php

declare(strict_types=1);

namespace IvanBaric\Plans\Actions;

use IvanBaric\Plans\Services\PlanDefinitionSynchronizer;

final readonly class SyncPlansFromConfigAction
{
    public function __construct(
        private PlanDefinitionSynchronizer $synchronizer,
    ) {}

    /**
     * @return array{created: int, updated: int, skipped: int}
     */
    public function handle(bool $deactivateMissing = false, ?bool $overwriteExisting = null): array
    {
        return $this->synchronizer->sync($deactivateMissing, $overwriteExisting);
    }
}
