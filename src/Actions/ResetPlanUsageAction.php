<?php

declare(strict_types=1);

namespace IvanBaric\Plans\Actions;

use IvanBaric\Corexis\Data\ActionResult;
use IvanBaric\Plans\Contracts\CurrentTeamResolver;
use IvanBaric\Plans\Data\UsagePeriod;
use IvanBaric\Plans\Events\PlanUsageReset;
use IvanBaric\Plans\Models\EntitlementUsage;
use IvanBaric\Plans\Models\PlanKey;

final readonly class ResetPlanUsageAction
{
    public function __construct(private CurrentTeamResolver $currentTeamResolver) {}

    public function handle(string $key, ?UsagePeriod $period = null): ActionResult
    {
        if ($result = corexis_authorization_result('plans.usage.reset')) {
            return $result;
        }

        $planKey = PlanKey::query()
            ->where('key', $key)
            ->where('is_active', true)
            ->first();

        if (! $planKey instanceof PlanKey) {
            return ActionResult::error(
                message: __('plans::messages.key_not_registered'),
                code: 'plan_key_not_found',
            );
        }

        $owner = $this->currentTeamResolver->current();

        EntitlementUsage::query()->updateOrCreate(
            [
                'owner_type' => $owner->getMorphClass(),
                'owner_id' => $owner->getKey(),
                'plan_key_id' => $planKey->getKey(),
                'period_started_at' => $period?->startsAt,
                'period_ends_at' => $period?->endsAt,
            ],
            [
                'used' => 0,
                'synced_at' => now(),
            ],
        );

        event(new PlanUsageReset($owner, $planKey));

        return ActionResult::success(__('plans::messages.usage_reset'));
    }
}
