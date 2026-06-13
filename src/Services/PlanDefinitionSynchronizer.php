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
    /**
     * @return array{created: int, updated: int, skipped: int}
     */
    public function sync(bool $deactivateMissing = false, ?bool $overwriteExisting = null): array
    {
        $overwriteExisting ??= (bool) config('plans.sync.overwrite_existing', false);

        return DB::transaction(function () use ($deactivateMissing, $overwriteExisting): array {
            $result = [
                'created' => 0,
                'updated' => 0,
                'skipped' => 0,
            ];

            $keys = $this->syncKeys(
                keys: (array) config('plans.keys', []),
                deactivateMissing: $deactivateMissing || (bool) config('plans.sync.deactivate_missing_keys', false),
                overwriteExisting: $overwriteExisting,
                result: $result,
            );

            $this->syncPlans(
                plans: (array) config('plans.plans', []),
                keys: $keys,
                deactivateMissing: $deactivateMissing || (bool) config('plans.sync.deactivate_missing_plans', false),
                overwriteExisting: $overwriteExisting,
                result: $result,
            );

            return $result;
        });
    }

    /**
     * @param  array<string, array<string, mixed>>  $keys
     * @param  array{created: int, updated: int, skipped: int}  $result
     * @return array<string, PlanKey>
     */
    private function syncKeys(array $keys, bool $deactivateMissing, bool $overwriteExisting, array &$result): array
    {
        $created = [];

        foreach ($keys as $key => $data) {
            $normalizedKey = $this->normalizeKey($key);
            $payload = [
                'name_key' => $data['name_key'] ?? "plans::keys.{$normalizedKey}.name",
                'description_key' => $data['description_key'] ?? "plans::keys.{$normalizedKey}.description",
                'type' => $data['type'],
                'period' => $data['period'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ];

            $planKey = PlanKey::query()
                ->where('key', $key)
                ->first();

            if (! $planKey instanceof PlanKey) {
                $created[$key] = PlanKey::query()->create([
                    'key' => $key,
                    ...$payload,
                ]);
                $result['created']++;

                continue;
            }

            $created[$key] = $planKey;

            if (! $overwriteExisting) {
                $result['skipped']++;

                continue;
            }

            $planKey->forceFill($payload);

            if (! $planKey->isDirty()) {
                $result['skipped']++;

                continue;
            }

            $planKey->save();
            $result['updated']++;
        }

        if ($deactivateMissing && $overwriteExisting) {
            $result['updated'] += PlanKey::query()
                ->whereNotIn('key', array_keys($keys))
                ->where('is_active', true)
                ->update(['is_active' => false]);
        } elseif ($deactivateMissing) {
            $result['skipped'] += PlanKey::query()
                ->whereNotIn('key', array_keys($keys))
                ->where('is_active', true)
                ->count();
        }

        return $created;
    }

    /**
     * @param  array<string, array<string, mixed>>  $plans
     * @param  array<string, PlanKey>  $keys
     * @param  array{created: int, updated: int, skipped: int}  $result
     */
    private function syncPlans(array $plans, array $keys, bool $deactivateMissing, bool $overwriteExisting, array &$result): void
    {
        foreach ($plans as $slug => $data) {
            $payload = [
                'name_key' => $data['name_key'] ?? "plans::plans.{$slug}.name",
                'description_key' => $data['description_key'] ?? "plans::plans.{$slug}.description",
                'is_active' => $data['is_active'] ?? true,
                'is_public' => $data['is_public'] ?? true,
                'sort_order' => $data['sort_order'] ?? 0,
                'prices' => $data['prices'] ?? null,
            ];

            $plan = SubscriptionPlan::query()
                ->where('slug', $slug)
                ->first();

            if (! $plan instanceof SubscriptionPlan) {
                $plan = SubscriptionPlan::query()->create([
                    'slug' => $slug,
                    ...$payload,
                ]);
                $result['created']++;
            } elseif ($overwriteExisting) {
                $plan->forceFill($payload);

                if ($plan->isDirty()) {
                    $plan->save();
                    $result['updated']++;
                } else {
                    $result['skipped']++;
                }
            } else {
                $result['skipped']++;
            }

            $syncedKeyIds = [];

            foreach ((array) ($data['entitlements'] ?? []) as $key => $value) {
                if (! isset($keys[$key])) {
                    if ((bool) config('plans.sync.fail_if_plan_entitlement_references_unknown_key', true)) {
                        throw new RuntimeException(__('plans::messages.unknown_entitlement_key', ['key' => $key]));
                    }

                    continue;
                }

                $syncedKeyIds[] = $keys[$key]->getKey();

                $entitlement = PlanEntitlement::query()
                    ->where('plan_id', $plan->getKey())
                    ->where('plan_key_id', $keys[$key]->getKey())
                    ->first();

                if (! $entitlement instanceof PlanEntitlement) {
                    PlanEntitlement::query()->create([
                        'plan_id' => $plan->getKey(),
                        'plan_key_id' => $keys[$key]->getKey(),
                        'value' => $this->normalizeValue($value),
                    ]);
                    $result['created']++;

                    continue;
                }

                if (! $overwriteExisting) {
                    $result['skipped']++;

                    continue;
                }

                $entitlement->forceFill([
                    'value' => $this->normalizeValue($value),
                ]);

                if (! $entitlement->isDirty()) {
                    $result['skipped']++;

                    continue;
                }

                $entitlement->save();
                $result['updated']++;
            }

            if ($overwriteExisting && (bool) config('plans.sync.delete_missing_entitlements', false)) {
                $result['updated'] += PlanEntitlement::query()
                    ->where('plan_id', $plan->getKey())
                    ->when($syncedKeyIds !== [], fn ($query) => $query->whereNotIn('plan_key_id', $syncedKeyIds))
                    ->when($syncedKeyIds === [], fn ($query) => $query)
                    ->delete();
            } elseif (! $overwriteExisting && (bool) config('plans.sync.delete_missing_entitlements', false)) {
                $result['skipped'] += PlanEntitlement::query()
                    ->where('plan_id', $plan->getKey())
                    ->when($syncedKeyIds !== [], fn ($query) => $query->whereNotIn('plan_key_id', $syncedKeyIds))
                    ->when($syncedKeyIds === [], fn ($query) => $query)
                    ->count();
            }
        }

        if ($deactivateMissing && $overwriteExisting) {
            $result['updated'] += SubscriptionPlan::query()
                ->whereNotIn('slug', array_keys($plans))
                ->where('is_active', true)
                ->update(['is_active' => false]);
        } elseif ($deactivateMissing) {
            $result['skipped'] += SubscriptionPlan::query()
                ->whereNotIn('slug', array_keys($plans))
                ->where('is_active', true)
                ->count();
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
