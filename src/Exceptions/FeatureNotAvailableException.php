<?php

declare(strict_types=1);

namespace IvanBaric\Plans\Exceptions;

use RuntimeException;

final class FeatureNotAvailableException extends RuntimeException
{
    public static function forFeature(string $feature): self
    {
        return new self(__('plans::messages.feature_not_available', [
            'feature' => __("plans::keys.{$feature}.name"),
        ]));
    }
}
