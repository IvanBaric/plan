<?php

declare(strict_types=1);

namespace IvanBaric\Plans\Services;

use Illuminate\Support\Collection;
use IvanBaric\Plans\Contracts\BillingResolver;
use IvanBaric\Plans\Data\PlanResult;
use IvanBaric\Plans\Models\PlanEntitlement;
use IvanBaric\Plans\Models\PlanKey;

final readonly class PlanInspector
{
    public function __construct(
        private BillingResolver $billing,
        private PlanUsageSynchronizer $usageSynchronizer,
    ) {}

    public function inspect(string $key, ?string $mode = null): PlanResult
    {
        $mode ??= (string) config('plans.access.default_inspection_mode', 'write');

        if (! (bool) config('plans.enabled', true)) {
            return PlanResult::deny(
                key: $key,
                type: 'unknown',
                message: $this->message('package_disabled'),
            );
        }

        if (! $this->billing->canAccessApplication()) {
            return PlanResult::deny(
                key: $key,
                type: 'unknown',
                message: $this->message('subscription_not_active'),
            );
        }

        if ($mode === 'write' && ! $this->billing->canPerformWriteActions()) {
            return PlanResult::deny(
                key: $key,
                type: 'unknown',
                message: $this->message('write_actions_disabled_in_grace'),
            );
        }

        $planKey = PlanKey::query()
            ->where('key', $key)
            ->where('is_active', true)
            ->first();

        if (! $planKey instanceof PlanKey) {
            return PlanResult::deny(
                key: $key,
                type: 'unknown',
                message: $this->message('key_not_registered'),
            );
        }

        $plan = $this->billing->plan();

        if ($plan === null || ! $plan->is_active) {
            return PlanResult::deny(
                key: $planKey->key,
                type: $planKey->type,
                message: $this->message('plan_not_found'),
            );
        }

        $entitlement = $plan->entitlements()
            ->where('plan_key_id', $planKey->getKey())
            ->first();

        if (! $entitlement instanceof PlanEntitlement) {
            return PlanResult::deny(
                key: $planKey->key,
                type: $planKey->type,
                message: $this->message('entitlement_not_available'),
            );
        }

        if ($planKey->isBoolean()) {
            return filter_var($entitlement->value, FILTER_VALIDATE_BOOLEAN)
                ? PlanResult::allow(
                    key: $planKey->key,
                    type: $planKey->type,
                    value: true,
                )
                : PlanResult::deny(
                    key: $planKey->key,
                    type: $planKey->type,
                    message: $this->message('boolean_denied'),
                    value: false,
                );
        }

        if ($planKey->type === 'value') {
            return PlanResult::allow(
                key: $planKey->key,
                type: $planKey->type,
                value: $entitlement->value,
            );
        }

        $used = $this->usageFor($planKey);

        if ($this->isUnlimited($entitlement->value)) {
            return PlanResult::allow(
                key: $planKey->key,
                type: $planKey->type,
                value: $entitlement->value,
                used: $used,
                limit: null,
            );
        }

        $limit = (int) $entitlement->value;

        if ($used >= $limit) {
            return PlanResult::deny(
                key: $planKey->key,
                type: $planKey->type,
                message: $this->message('limit_reached', [
                    'used' => $used,
                    'limit' => $limit,
                ]),
                value: $entitlement->value,
                used: $used,
                limit: $limit,
            );
        }

        return PlanResult::allow(
            key: $planKey->key,
            type: $planKey->type,
            value: $entitlement->value,
            used: $used,
            limit: $limit,
        );
    }

    /**
     * @return Collection<int, PlanResult>
     */
    public function syncAll(): Collection
    {
        return PlanKey::query()
            ->where('is_active', true)
            ->get()
            ->filter(fn (PlanKey $key): bool => $key->usesUsage() || (bool) config('plans.sync.sync_boolean_keys', false))
            ->values()
            ->map(fn (PlanKey $key): PlanResult => $this->inspect($key->key, 'read'));
    }

    private function usageFor(PlanKey $key): int
    {
        if ((bool) config('plans.sync.sync_on_inspect', true)) {
            return $this->usageSynchronizer->sync($key);
        }

        return $this->usageSynchronizer->stored($key);
    }

    private function isUnlimited(int|string|bool|null $value): bool
    {
        $unlimited = (array) config('plans.unlimited_values', []);
        $stringValues = array_map(static fn (mixed $item): string => (string) $item, $unlimited);

        return in_array($value, $unlimited, true)
            || in_array((string) $value, $stringValues, true);
    }

    private function message(string $key, array $replace = []): string
    {
        $translationKey = config("plans.translation.messages.{$key}", "plans::messages.{$key}");

        return __($translationKey, $replace);
    }
}
