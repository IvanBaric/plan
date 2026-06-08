<?php

declare(strict_types=1);

namespace IvanBaric\Plans\Data;

use Illuminate\Support\Carbon;

final readonly class UsagePeriod
{
    public function __construct(
        public Carbon $startsAt,
        public Carbon $endsAt,
    ) {}

    public static function monthly(?Carbon $now = null): self
    {
        $now ??= Carbon::now();

        return new self(
            startsAt: $now->copy()->startOfMonth(),
            endsAt: $now->copy()->endOfMonth(),
        );
    }

    public static function yearly(?Carbon $now = null): self
    {
        $now ??= Carbon::now();

        return new self(
            startsAt: $now->copy()->startOfYear(),
            endsAt: $now->copy()->endOfYear(),
        );
    }
}
