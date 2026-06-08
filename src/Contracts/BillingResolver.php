<?php

declare(strict_types=1);

namespace IvanBaric\Plans\Contracts;

use IvanBaric\Plans\Models\SubscriptionPlan;

interface BillingResolver
{
    public function plan(): ?SubscriptionPlan;

    public function status(): string;

    public function canAccessApplication(): bool;

    public function canPerformWriteActions(): bool;
}
