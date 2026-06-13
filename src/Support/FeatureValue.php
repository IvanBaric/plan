<?php

declare(strict_types=1);

namespace IvanBaric\Plans\Support;

final readonly class FeatureValue
{
    public static function normalize(mixed $value): mixed
    {
        if (is_array($value) && array_key_exists('value', $value)) {
            return $value['value'];
        }

        return $value;
    }

    public static function display(mixed $value): string
    {
        if ($value === null || in_array($value, [-1, '-1', 'unlimited', '*'], true)) {
            return __('plans::admin.labels.unlimited');
        }

        if (is_bool($value)) {
            return $value
                ? __('plans::admin.labels.enabled')
                : __('plans::admin.labels.disabled');
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
        }

        return (string) $value;
    }
}
