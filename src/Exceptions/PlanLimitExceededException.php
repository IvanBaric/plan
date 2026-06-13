<?php

declare(strict_types=1);

namespace IvanBaric\Plans\Exceptions;

use RuntimeException;

final class PlanLimitExceededException extends RuntimeException
{
    public static function forFeature(string $feature, int $limit, int $used): self
    {
        return new self(__('plans::messages.limit_exceeded', [
            'feature' => __("plans::keys.{$feature}.name"),
            'limit' => $limit,
            'used' => $used,
        ]));
    }
}
