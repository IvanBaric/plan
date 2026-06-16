<?php

declare(strict_types=1);

namespace IvanBaric\Plans\Actions;

use IvanBaric\Corexis\Data\ActionResult;
use IvanBaric\Plans\Events\PlansSynced;
use RuntimeException;

final readonly class SyncPlansAction
{
    public function __construct(private SyncPlansFromConfigAction $syncPlans) {}

    public function handle(bool $deactivateMissing = false, ?bool $overwriteExisting = null): ActionResult
    {
        if ($result = corexis_authorization_result('plans.manage')) {
            return $result;
        }

        try {
            $result = $this->syncPlans->handle($deactivateMissing, $overwriteExisting);
        } catch (RuntimeException $exception) {
            return ActionResult::error($exception->getMessage(), 'plans_sync_failed');
        }

        event(new PlansSynced($result));

        return ActionResult::success(
            message: __('plans::messages.sync_finished'),
            data: $result,
        );
    }
}
