<?php

declare(strict_types=1);

namespace IvanBaric\Plans\Commands;

use Illuminate\Console\Command;
use IvanBaric\Plans\Services\PlanDefinitionSynchronizer;

final class SyncPlansCommand extends Command
{
    protected $signature = 'plans:sync {--deactivate-missing}';

    public function __construct()
    {
        parent::__construct();

        $this->setDescription(__('plans::messages.sync_command_description'));
    }

    public function handle(PlanDefinitionSynchronizer $synchronizer): int
    {
        $synchronizer->sync(
            deactivateMissing: (bool) $this->option('deactivate-missing'),
        );

        $this->components->info(__('plans::messages.sync_finished'));

        return self::SUCCESS;
    }
}
