<?php

declare(strict_types=1);

namespace IvanBaric\Plans\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use IvanBaric\Corexis\Contracts\Events\DomainEvent;

final readonly class PlansSynced implements DomainEvent, ShouldDispatchAfterCommit
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  array{created: int, updated: int, skipped: int}  $result
     */
    public function __construct(public array $result) {}
}
