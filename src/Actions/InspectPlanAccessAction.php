<?php

declare(strict_types=1);

namespace IvanBaric\Plans\Actions;

use IvanBaric\Corexis\Data\ActionResult;
use IvanBaric\Plans\Services\PlanInspector;

final readonly class InspectPlanAccessAction
{
    public function __construct(private PlanInspector $inspector) {}

    public function handle(string $key, ?string $mode = null): ActionResult
    {
        $result = $this->inspector->inspect($key, $mode);

        if ($result->denied()) {
            return ActionResult::error(
                message: $result->message(),
                code: 'plan_access_denied',
                data: $result,
            );
        }

        return ActionResult::success(
            message: $result->message(),
            data: $result,
        );
    }
}
