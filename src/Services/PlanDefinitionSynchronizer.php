<?php

declare(strict_types=1);

namespace IvanBaric\Plans\Services;

use Illuminate\Support\Facades\DB;
use IvanBaric\Plans\Models\PlanEntitlement;
use IvanBaric\Plans\Models\PlanKey;
use IvanBaric\Plans\Models\SubscriptionPlan;
use RuntimeException;

final readonly class PlanDefinitionSynchronizer
{
    public function sync(bool $deactivateMissing = false): void
    {
        DB::transaction(function () use ($deactivateMissing): void {
            $keys = $this->syncKeys(
                keys: (array) config('plans.keys', []),
                deactivateMissing: $deactivateMissing || (bool) config('plans.sync.deactivate_missing_keys', false),
            );

            $this->syncPlans(
                plans: (array) config('plans.plans', []),
                keys: $keys,
                deactivateMissing: $deactivateMissing || (bool) config('plans.sync.deactivate_missing_plans', false),
            );
        });
    }

    /**
     * @param  array<string, array<string, mixed>>  $keys
     * @return array<string, PlanKey>
     */
    private function syncKeys(array $keys, bool $deactivateMissing): array
    {
        $created = [];

        foreach ($keys as $key => $data) {
            $normalizedKey = $this->normalizeKey($key);

            $created[$key] = PlanKey::query()->updateOrCreate(
                ['key' => $key],
                [
                    'name_key' => $data['name_key'] ?? "plans::keys.{$normalizedKey}.name",
                    'description_key' => $data['description_key'] ?? "plans::keys.{$normalizedKey}.description",
                    'type' => $data['type'],
                    'period' => $data['period'] ?? null,
                    'is_active' => $data['is_active'] ?? true,
                ],
            );
        }

        if ($deactivateMissing) {
            PlanKey::query()
                ->whereNotIn('key', array_keys($keys))
                ->update(['is_active' => false]);
        }

        return $created;
    }

    /**
     * @param  array<string, array<string, mixed>>  $plans
     * @param  array<string, PlanKey>  $keys
     */
    private function syncPlans(array $plans, array $keys, bool $deactivateMissing): void
    {
        foreach ($plans as $slug => $data) {
            $plan = SubscriptionPlan::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name_key' => $data['name_key'] ?? "plans::plans.{$slug}.name",
                    'description_key' => $data['description_key'] ?? "plans::plans.{$slug}.description",
                    'is_active' => $data['is_active'] ?? true,
                    'is_public' => $data['is_public'] ?? true,
                    'sort_order' => $data['sort_order'] ?? 0,
                    'prices' => $data['prices'] ?? null,
                ],
            );

            $syncedKeyIds = [];

            foreach ((array) ($data['entitlements'] ?? []) as $key => $value) {
                if (! isset($keys[$key])) {
                    if ((bool) config('plans.sync.fail_if_plan_entitlement_references_unknown_key', true)) {
                        throw new RuntimeException(__('plans::messages.unknown_entitlement_key', ['key' => $key]));
                    }

                    continue;
                }

                $syncedKeyIds[] = $keys[$key]->getKey();

                PlanEntitlement::query()->updateOrCreate(
                    [
                        'plan_id' => $plan->getKey(),
                        'plan_key_id' => $keys[$key]->getKey(),
                    ],
                    [
                        'value' => $this->normalizeValue($value),
                    ],
                );
            }

            if ((bool) config('plans.sync.delete_missing_entitlements', false)) {
                PlanEntitlement::query()
                    ->where('plan_id', $plan->getKey())
                    ->when($syncedKeyIds !== [], fn ($query) => $query->whereNotIn('plan_key_id', $syncedKeyIds))
                    ->when($syncedKeyIds === [], fn ($query) => $query)
                    ->delete();
            }
        }

        if ($deactivateMissing) {
            SubscriptionPlan::query()
                ->whereNotIn('slug', array_keys($plans))
                ->update(['is_active' => false]);
        }
    }

    private function normalizeValue(int|bool|string $value): string
    {
        return match (true) {
            is_bool($value) => $value ? '1' : '0',
            default => (string) $value,
        };
    }

    private function normalizeKey(string $key): string
    {
        return str_replace('.', '_', $key);
    }
}
