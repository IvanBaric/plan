<?php

declare(strict_types=1);

namespace IvanBaric\Plans\Data;

final readonly class PlanDefinition
{
    /**
     * @param  array<string, mixed>  $features
     */
    public function __construct(
        public string $code,
        public string $name,
        public int $monthlyPriceEur,
        public int $yearlyPriceEur,
        public array $features,
    ) {}

    public function value(string $feature): mixed
    {
        return $this->features[$feature] ?? null;
    }

    public function enabled(string $feature): bool
    {
        $value = $this->value($feature);

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value > 0;
        }

        return $value !== null;
    }

    public function integerLimit(string $feature): ?int
    {
        $value = $this->value($feature);

        return is_int($value) ? $value : null;
    }

    public function isUnlimited(string $feature): bool
    {
        return array_key_exists($feature, $this->features)
            && $this->features[$feature] === null;
    }
}
