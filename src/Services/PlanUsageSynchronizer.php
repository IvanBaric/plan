<?php

declare(strict_types=1);

namespace IvanBaric\Plans\Services;

use Illuminate\Database\Eloquent\Model;
use IvanBaric\Plans\Contracts\CurrentTeamResolver;
use IvanBaric\Plans\Contracts\PlanUsageResolver;
use IvanBaric\Plans\Data\UsagePeriod;
use IvanBaric\Plans\Models\EntitlementUsage;
use IvanBaric\Plans\Models\PlanKey;

final readonly class PlanUsageSynchronizer
{
    public function __construct(
        private CurrentTeamResolver $currentTeamResolver,
        private PlanUsageResolver $usageResolver,
    ) {}

    public function sync(PlanKey $key): int
    {
        if (! $key->usesUsage()) {
            return 0;
        }

        $owner = $this->currentTeamResolver->current();
        $period = $this->periodFor($key);

        $used = max(0, $this->usageResolver->used(
            owner: $owner,
            key: $key,
            period: $period,
        ));

        if ((bool) config('plans.sync.store_synced_usage', true)) {
            $this->store(
                owner: $owner,
                key: $key,
                used: $used,
                period: $period,
            );
        }

        return $used;
    }

    public function stored(PlanKey $key): int
    {
        $owner = $this->currentTeamResolver->current();
        $period = $this->periodFor($key);

        return (int) (EntitlementUsage::query()
            ->where($this->usageIdentity($owner, $key, $period))
            ->value('used') ?? 0);
    }

    private function periodFor(PlanKey $key): ?UsagePeriod
    {
        if (! $key->isMetered()) {
            return null;
        }

        return match ($key->period) {
            'monthly' => UsagePeriod::monthly(),
            'yearly' => UsagePeriod::yearly(),
            default => null,
        };
    }

    private function store(Model $owner, PlanKey $key, int $used, ?UsagePeriod $period): void
    {
        $identity = $this->usageIdentity($owner, $key, $period);
        $values = [
            'used' => $used,
            'synced_at' => now(),
        ];

        if ((bool) config('plans.sync.create_missing_usage_rows', true)) {
            EntitlementUsage::query()->updateOrCreate($identity, $values);

            return;
        }

        EntitlementUsage::query()
            ->where($identity)
            ->update($values);
    }

    /**
     * @return array<string, mixed>
     */
    private function usageIdentity(Model $owner, PlanKey $key, ?UsagePeriod $period): array
    {
        return [
            'owner_type' => $owner->getMorphClass(),
            'owner_id' => $owner->getKey(),
            'plan_key_id' => $key->getKey(),
            'period_started_at' => $period?->startsAt,
            'period_ends_at' => $period?->endsAt,
        ];
    }
}
