<?php

declare(strict_types=1);

namespace IvanBaric\Plans\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \IvanBaric\Plans\Data\PlanResult inspect(string $key, ?string $mode = null)
 * @method static bool allows(string $key, ?string $mode = null)
 * @method static bool denies(string $key, ?string $mode = null)
 * @method static int|null usage(string $key, ?string $mode = null)
 * @method static int|null limit(string $key, ?string $mode = null)
 * @method static int|null remaining(string $key, ?string $mode = null)
 * @method static int percentage(string $key, ?string $mode = null)
 * @method static \IvanBaric\Plans\Data\PlanResult sync(string $key)
 * @method static \Illuminate\Support\Collection syncAll()
 * @method static void syncDefinitions(bool $deactivateMissing = false)
 */
final class Plan extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'ivanbaric.plans';
    }
}
