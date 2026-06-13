<?php

declare(strict_types=1);

namespace IvanBaric\Plans;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\ServiceProvider;
use IvanBaric\Plans\Commands\InstallPlansCommand;
use IvanBaric\Plans\Commands\SyncPlansCommand;
use IvanBaric\Plans\Contracts\BillingResolver as BillingResolverContract;
use IvanBaric\Plans\Contracts\CurrentPlanResolver as CurrentPlanResolverContract;
use IvanBaric\Plans\Contracts\CurrentTeamResolver as CurrentTeamResolverContract;
use IvanBaric\Plans\Contracts\PlanRepository as PlanRepositoryContract;
use IvanBaric\Plans\Contracts\PlanUsageResolver as PlanUsageResolverContract;
use IvanBaric\Plans\Contracts\UsageCounter as UsageCounterContract;
use IvanBaric\Plans\Managers\PlanManager;
use IvanBaric\Plans\Services\ConfigPlanRepository;
use IvanBaric\Plans\Services\DefaultCurrentPlanResolver;
use IvanBaric\Plans\Services\EloquentUsageCounter;
use IvanBaric\Plans\Services\EntitlementService;

final class PlansServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/plans.php', 'plans');

        $this->app->singleton('ivanbaric.plans', PlanManager::class);
        $this->app->singleton(EntitlementService::class);
        $this->app->singleton(PlanRepositoryContract::class, ConfigPlanRepository::class);
        $this->app->singleton(UsageCounterContract::class, EloquentUsageCounter::class);

        $this->app->scoped(CurrentPlanResolverContract::class, function ($app): CurrentPlanResolverContract {
            $resolver = config('plans.billing.resolver', DefaultCurrentPlanResolver::class);

            if (! is_string($resolver) || $resolver === '') {
                throw new BindingResolutionException('plans.billing.resolver');
            }

            return $app->make($resolver);
        });

        $this->app->scoped(CurrentTeamResolverContract::class, function ($app): CurrentTeamResolverContract {
            return $app->make($this->resolverClass('current_team'));
        });

        $this->app->scoped(BillingResolverContract::class, function ($app): BillingResolverContract {
            return $app->make($this->resolverClass('billing'));
        });

        $this->app->scoped(PlanUsageResolverContract::class, function ($app): PlanUsageResolverContract {
            return $app->make($this->resolverClass('usage'));
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'plans');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'plans');

        if ((bool) config('plans.admin.enabled', true)) {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        }

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/plans.php' => config_path('plans.php'),
        ], 'plans-config');

        $this->publishes([
            __DIR__.'/../stubs/Resolvers/CurrentTeamResolver.stub' => app_path('Resolvers/CurrentTeamResolver.php'),
            __DIR__.'/../stubs/Resolvers/BillingResolver.stub' => app_path('Resolvers/BillingResolver.php'),
            __DIR__.'/../stubs/Resolvers/PlanUsageResolver.stub' => app_path('Resolvers/PlanUsageResolver.php'),
        ], 'plans-resolvers');

        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'plans-migrations');

        $this->publishes([
            __DIR__.'/../lang' => $this->app->langPath('vendor/plans'),
        ], 'plans-translations');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/plans'),
        ], 'plans-views');

        $this->commands([
            InstallPlansCommand::class,
            SyncPlansCommand::class,
        ]);
    }

    /**
     * @throws BindingResolutionException
     */
    private function resolverClass(string $key): string
    {
        $resolver = config("plans.resolvers.{$key}");

        if (! is_string($resolver) || $resolver === '') {
            throw new BindingResolutionException("plans.resolvers.{$key}");
        }

        return $resolver;
    }
}
