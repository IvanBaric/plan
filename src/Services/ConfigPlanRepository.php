<?php

declare(strict_types=1);

namespace IvanBaric\Plans\Services;

use InvalidArgumentException;
use IvanBaric\Plans\Contracts\PlanRepository;
use IvanBaric\Plans\Data\PlanDefinition;

final class ConfigPlanRepository implements PlanRepository
{
    /**
     * @return array<string, PlanDefinition>
     */
    public function all(): array
    {
        return collect((array) config('plans.plans', []))
            ->mapWithKeys(function (array $definition, string $code): array {
                return [$code => $this->fromConfig($code, $definition)];
            })
            ->all();
    }

    public function find(string $code): ?PlanDefinition
    {
        $definition = config("plans.plans.{$code}");

        return is_array($definition) ? $this->fromConfig($code, $definition) : null;
    }

    public function get(string $code): PlanDefinition
    {
        return $this->find($code)
            ?? throw new InvalidArgumentException("Plan [{$code}] is not configured.");
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    private function fromConfig(string $code, array $definition): PlanDefinition
    {
        return new PlanDefinition(
            code: $code,
            name: (string) ($definition['name'] ?? $code),
            monthlyPriceEur: (int) data_get($definition, 'prices.monthly.amount', $definition['monthly_price_eur'] ?? 0),
            yearlyPriceEur: (int) data_get($definition, 'prices.yearly.amount', $definition['yearly_price_eur'] ?? 0),
            features: (array) ($definition['entitlements'] ?? $definition['features'] ?? []),
        );
    }
}
