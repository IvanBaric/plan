<?php

declare(strict_types=1);

namespace IvanBaric\Plans\Actions;

use Illuminate\Support\Facades\DB;
use IvanBaric\Corexis\Data\ActionResult;
use IvanBaric\Plans\Contracts\CurrentTeamResolver;
use IvanBaric\Plans\Data\UsagePeriod;
use IvanBaric\Plans\Events\PlanUsageRecorded;
use IvanBaric\Plans\Models\EntitlementUsage;
use IvanBaric\Plans\Models\PlanKey;

final readonly class RecordPlanUsageAction
{
    public function __construct(private CurrentTeamResolver $currentTeamResolver) {}

    public function handle(string $key, int $used, ?UsagePeriod $period = null): ActionResult
    {
        if ($result = corexis_authorization_result('plans.usage.record')) {
            return $result;
        }

        $planKey = $this->resolveKey($key);

        if (! $planKey instanceof PlanKey) {
            return ActionResult::error(
                message: __('plans::messages.key_not_registered'),
                code: 'plan_key_not_found',
            );
        }

        $owner = $this->currentTeamResolver->current();
        $used = max(0, $used);

        $usage = DB::transaction(function () use ($owner, $period, $planKey, $used): EntitlementUsage {
            /** @var PlanKey $planKey */
            $planKey = PlanKey::query()
                ->whereKey($planKey->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $identity = [
                'owner_type' => $owner->getMorphClass(),
                'owner_id' => $owner->getKey(),
                'plan_key_id' => $planKey->getKey(),
                'period_started_at' => $period?->startsAt,
                'period_ends_at' => $period?->endsAt,
            ];

            $usage = EntitlementUsage::query()
                ->where($identity)
                ->lockForUpdate()
                ->first();

            if (! $usage instanceof EntitlementUsage) {
                $usage = new EntitlementUsage($identity);
            }

            $usage->forceFill([
                'used' => $used,
                'synced_at' => now(),
            ])->save();

            return $usage->refresh();
        });

        event(new PlanUsageRecorded($owner, $planKey, $used));

        return ActionResult::success(
            message: __('plans::messages.usage_synced'),
            data: $usage,
        );
    }

    private function resolveKey(string $key): ?PlanKey
    {
        return PlanKey::query()
            ->where('key', $key)
            ->where('is_active', true)
            ->first();
    }
}
