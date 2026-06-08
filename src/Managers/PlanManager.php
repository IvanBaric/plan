<?php

declare(strict_types=1);

namespace IvanBaric\Plans\Managers;

use Illuminate\Support\Collection;
use IvanBaric\Plans\Data\PlanResult;
use IvanBaric\Plans\Services\PlanDefinitionSynchronizer;
use IvanBaric\Plans\Services\PlanInspector;

final readonly class PlanManager
{
    public function __construct(
        private PlanInspector $inspector,
        private PlanDefinitionSynchronizer $definitions,
    ) {}

    public function inspect(string $key, ?string $mode = null): PlanResult
    {
        return $this->inspector->inspect($key, $mode);
    }

    public function allows(string $key, ?string $mode = null): bool
    {
        return $this->inspect($key, $mode)->allowed();
    }

    public function denies(string $key, ?string $mode = null): bool
    {
        return $this->inspect($key, $mode)->denied();
    }

    public function usage(string $key, ?string $mode = null): ?int
    {
        return $this->inspect($key, $mode)->used();
    }

    public function limit(string $key, ?string $mode = null): ?int
    {
        return $this->inspect($key, $mode)->limit();
    }

    public function remaining(string $key, ?string $mode = null): ?int
    {
        return $this->inspect($key, $mode)->remaining();
    }

    public function percentage(string $key, ?string $mode = null): int
    {
        return $this->inspect($key, $mode)->percentage();
    }

    public function sync(string $key): PlanResult
    {
        return $this->inspect($key, 'read');
    }

    /**
     * @return Collection<int, PlanResult>
     */
    public function syncAll(): Collection
    {
        return $this->inspector->syncAll();
    }

    public function syncDefinitions(bool $deactivateMissing = false): void
    {
        $this->definitions->sync($deactivateMissing);
    }
}
