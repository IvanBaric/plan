<?php

declare(strict_types=1);

namespace IvanBaric\Plans\Commands;

use Illuminate\Console\Command;

final class InstallPlansCommand extends Command
{
    protected $signature = 'plans:install {--force} {--migrate} {--sync}';

    public function __construct()
    {
        parent::__construct();

        $this->setDescription(__('plans::messages.install_command_description'));
    }

    public function handle(): int
    {
        $force = (bool) $this->option('force');

        $this->call('vendor:publish', [
            '--tag' => 'plans-config',
            '--force' => $force,
        ]);

        $this->call('vendor:publish', [
            '--tag' => 'plans-migrations',
            '--force' => $force,
        ]);

        $this->call('vendor:publish', [
            '--tag' => 'plans-resolvers',
            '--force' => $force,
        ]);

        $this->call('vendor:publish', [
            '--tag' => 'plans-translations',
            '--force' => $force,
        ]);

        if ((bool) $this->option('migrate')) {
            $this->call('migrate');
        }

        $shouldSync = (bool) $this->option('sync')
            || ((bool) config('plans.sync.sync_definitions_on_install', true) && (bool) $this->option('migrate'));

        if ($shouldSync) {
            $this->call('plans:sync', [
                '--force' => $force,
            ]);
        }

        $this->components->info(__('plans::messages.install_finished'));

        return self::SUCCESS;
    }
}
