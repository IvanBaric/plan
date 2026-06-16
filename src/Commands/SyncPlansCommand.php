<?php

declare(strict_types=1);

namespace IvanBaric\Plans\Commands;

use Illuminate\Console\Command;
use IvanBaric\Plans\Actions\SyncPlansAction;

final class SyncPlansCommand extends Command
{
    protected $signature = 'plans:sync
        {--deactivate-missing : Deactivate keys and plans missing from config when overwriting is enabled.}
        {--force : Overwrite existing plans, keys and entitlements from configuration.}';

    public function __construct()
    {
        parent::__construct();

        $this->setDescription(__('plans::messages.sync_command_description'));
    }

    public function handle(SyncPlansAction $syncPlans): int
    {
        $actionResult = $syncPlans->handle(
            deactivateMissing: (bool) $this->option('deactivate-missing'),
            overwriteExisting: (bool) $this->option('force') || (bool) config('plans.sync.overwrite_existing', false),
        );

        if ($actionResult->failed()) {
            $this->components->error($actionResult->message);

            return self::FAILURE;
        }

        $result = is_array($actionResult->data) ? $actionResult->data : ['created' => 0, 'updated' => 0, 'skipped' => 0];

        $this->components->info("created: {$result['created']}");
        $this->components->info("updated: {$result['updated']}");
        $this->components->info("skipped: {$result['skipped']}");
        $this->components->info(__('plans::messages.sync_finished'));

        return self::SUCCESS;
    }
}
