<?php

declare(strict_types=1);

namespace IvanBaric\Plans\Data;

final readonly class PlanResult
{
    public function __construct(
        private bool $allowed,
        private string $key,
        private string $type,
        private string $message = '',
        private int|string|bool|null $value = null,
        private ?int $used = null,
        private ?int $limit = null,
    ) {}

    public static function allow(
        string $key,
        string $type,
        int|string|bool|null $value = null,
        ?int $used = null,
        ?int $limit = null,
    ): self {
        return new self(
            allowed: true,
            key: $key,
            type: $type,
            value: $value,
            used: $used,
            limit: $limit,
        );
    }

    public static function deny(
        string $key,
        string $type,
        string $message,
        int|string|bool|null $value = null,
        ?int $used = null,
        ?int $limit = null,
    ): self {
        return new self(
            allowed: false,
            key: $key,
            type: $type,
            message: $message,
            value: $value,
            used: $used,
            limit: $limit,
        );
    }

    public function allowed(): bool
    {
        return $this->allowed;
    }

    public function denied(): bool
    {
        return ! $this->allowed;
    }

    public function key(): string
    {
        return $this->key;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function message(): string
    {
        return $this->message;
    }

    public function value(): int|string|bool|null
    {
        return $this->value;
    }

    public function used(): ?int
    {
        return $this->used;
    }

    public function limit(): ?int
    {
        return $this->limit;
    }

    public function remaining(): ?int
    {
        if ($this->used === null || $this->limit === null) {
            return null;
        }

        return max(0, $this->limit - $this->used);
    }

    public function percentage(): int
    {
        if ($this->used === null || $this->limit === null || $this->limit <= 0) {
            return 0;
        }

        return min(100, (int) round(($this->used / $this->limit) * 100));
    }
}
