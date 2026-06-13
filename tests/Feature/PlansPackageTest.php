<?php

declare(strict_types=1);

namespace IvanBaric\Plans\Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Schema;
use IvanBaric\Plans\Contracts\BillingResolver as BillingResolverContract;
use IvanBaric\Plans\Contracts\CurrentTeamResolver as CurrentTeamResolverContract;
use IvanBaric\Plans\Contracts\PlanUsageResolver as PlanUsageResolverContract;
use IvanBaric\Plans\Data\PlanResult;
use IvanBaric\Plans\Data\UsagePeriod;
use IvanBaric\Plans\Facades\Plan;
use IvanBaric\Plans\Models\EntitlementUsage;
use IvanBaric\Plans\Models\PlanEntitlement;
use IvanBaric\Plans\Models\PlanKey;
use IvanBaric\Plans\Models\SubscriptionPlan;
use IvanBaric\Plans\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class PlansPackageTest extends TestCase
{
    private PlansTestOwner $owner;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'plans.resolvers.current_team' => PlansCurrentTeamResolverFake::class,
            'plans.resolvers.billing' => PlansBillingResolverFake::class,
            'plans.resolvers.usage' => PlansUsageResolverFake::class,
        ]);

        $this->refreshPackageSchema();

        $this->owner = new PlansTestOwner;
        $this->owner->forceFill(['id' => 1001]);
        $this->owner->exists = true;

        PlansCurrentTeamResolverFake::$owner = $this->owner;
        PlansBillingResolverFake::$plan = null;
        PlansBillingResolverFake::$status = 'active';
        PlansUsageResolverFake::$usage = [];
        PlansUsageResolverFake::$lastPeriodByKey = [];

        Facade::clearResolvedInstance('ivanbaric.plans');
        Carbon::setTestNow();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_plans_sync_creates_definitions_and_is_idempotent(): void
    {
        Artisan::call('plans:sync');

        $this->assertSame(8, PlanKey::query()->count());
        $this->assertSame(3, SubscriptionPlan::query()->count());
        $this->assertSame(24, PlanEntitlement::query()->count());

        Artisan::call('plans:sync');

        $this->assertSame(8, PlanKey::query()->count());
        $this->assertSame(3, SubscriptionPlan::query()->count());
        $this->assertSame(24, PlanEntitlement::query()->count());
    }

    public function test_plans_safe_sync_creates_new_config_records(): void
    {
        Artisan::call('plans:sync');

        $keys = config('plans.keys');
        $plans = config('plans.plans');

        $keys['uploads.monthly'] = [
            'name_key' => 'plans::keys.uploads_monthly.name',
            'description_key' => 'plans::keys.uploads_monthly.description',
            'type' => 'metered',
            'period' => 'monthly',
            'is_active' => true,
        ];

        $plans['starter']['entitlements']['uploads.monthly'] = 50;

        config([
            'plans.keys' => $keys,
            'plans.plans' => $plans,
        ]);

        Artisan::call('plans:sync');

        $key = PlanKey::query()
            ->where('key', 'uploads.monthly')
            ->firstOrFail();
        $starter = $this->plan('starter');

        $this->assertSame('metered', $key->type);
        $this->assertSame('50', PlanEntitlement::query()
            ->where('plan_id', $starter->getKey())
            ->where('plan_key_id', $key->getKey())
            ->value('value'));
    }

    public function test_plans_safe_sync_does_not_overwrite_manual_runtime_changes(): void
    {
        Artisan::call('plans:sync');

        $starter = $this->plan('starter');
        $starter->forceFill([
            'name_key' => 'manual.plan.name',
            'is_public' => false,
            'prices' => [
                'monthly' => [
                    'amount' => 123,
                    'currency' => 'EUR',
                ],
            ],
        ])->save();

        $menus = PlanKey::query()->where('key', 'menus')->firstOrFail();
        PlanEntitlement::query()
            ->where('plan_id', $starter->getKey())
            ->where('plan_key_id', $menus->getKey())
            ->update(['value' => '999']);

        $plans = config('plans.plans');
        $plans['starter']['name_key'] = 'config.plan.name';
        $plans['starter']['is_public'] = true;
        $plans['starter']['prices'] = [
            'monthly' => [
                'amount' => 321,
                'currency' => 'EUR',
            ],
        ];
        $plans['starter']['entitlements']['menus'] = 42;

        config(['plans.plans' => $plans]);

        Artisan::call('plans:sync');

        $starter->refresh();

        $this->assertSame('manual.plan.name', $starter->name_key);
        $this->assertFalse($starter->is_public);
        $this->assertSame(123, $starter->prices['monthly']['amount']);
        $this->assertSame('999', PlanEntitlement::query()
            ->where('plan_id', $starter->getKey())
            ->where('plan_key_id', $menus->getKey())
            ->value('value'));
    }

    public function test_plans_force_sync_overwrites_runtime_changes_from_config(): void
    {
        Artisan::call('plans:sync');

        $starter = $this->plan('starter');
        $starter->forceFill([
            'name_key' => 'manual.plan.name',
            'is_public' => false,
            'prices' => [
                'monthly' => [
                    'amount' => 123,
                    'currency' => 'EUR',
                ],
            ],
        ])->save();

        $menus = PlanKey::query()->where('key', 'menus')->firstOrFail();
        PlanEntitlement::query()
            ->where('plan_id', $starter->getKey())
            ->where('plan_key_id', $menus->getKey())
            ->update(['value' => '999']);

        $plans = config('plans.plans');
        $plans['starter']['name_key'] = 'config.plan.name';
        $plans['starter']['is_public'] = true;
        $plans['starter']['prices'] = [
            'monthly' => [
                'amount' => 321,
                'currency' => 'EUR',
            ],
        ];
        $plans['starter']['entitlements']['menus'] = 42;

        config(['plans.plans' => $plans]);

        Artisan::call('plans:sync', ['--force' => true]);

        $starter->refresh();

        $this->assertSame('config.plan.name', $starter->name_key);
        $this->assertTrue($starter->is_public);
        $this->assertSame(321, $starter->prices['monthly']['amount']);
        $this->assertSame('42', PlanEntitlement::query()
            ->where('plan_id', $starter->getKey())
            ->where('plan_key_id', $menus->getKey())
            ->value('value'));
    }

    public function test_plan_key_identifier_remains_stable_during_force_sync(): void
    {
        Artisan::call('plans:sync');

        $menus = PlanKey::query()->where('key', 'menus')->firstOrFail();
        $keys = config('plans.keys');
        $keys['menus']['name_key'] = 'plans::keys.changed_menus.name';

        config(['plans.keys' => $keys]);

        Artisan::call('plans:sync', ['--force' => true]);

        $menus->refresh();

        $this->assertSame('menus', $menus->key);
        $this->assertSame('plans::keys.changed_menus.name', $menus->name_key);
        $this->assertSame(1, PlanKey::query()->where('key', 'menus')->count());
    }

    public function test_plans_sync_can_deactivate_missing_keys_and_plans(): void
    {
        Artisan::call('plans:sync');

        $starter = config('plans.plans.starter');
        $starter['entitlements'] = ['menus' => 1];

        config([
            'plans.keys' => [
                'menus' => config('plans.keys.menus'),
            ],
            'plans.plans' => [
                'starter' => $starter,
            ],
        ]);

        Artisan::call('plans:sync', [
            '--deactivate-missing' => true,
            '--force' => true,
        ]);

        $this->assertTrue((bool) PlanKey::query()->where('key', 'menus')->value('is_active'));
        $this->assertFalse((bool) PlanKey::query()->where('key', 'items')->value('is_active'));
        $this->assertTrue((bool) SubscriptionPlan::query()->where('slug', 'starter')->value('is_active'));
        $this->assertFalse((bool) SubscriptionPlan::query()->where('slug', 'pro')->value('is_active'));
    }

    public function test_boolean_plan_inspection_allows_true_and_denies_false(): void
    {
        $this->syncAndUsePlan('pro');

        $allowed = Plan::inspect('qr.svg_download');

        $this->assertTrue($allowed->allowed());
        $this->assertTrue($allowed->value());

        PlansBillingResolverFake::$plan = $this->plan('starter');

        $denied = Plan::inspect('qr.svg_download');

        $this->assertTrue($denied->denied());
        $this->assertFalse($denied->value());
        $this->assertSame(__('plans::messages.boolean_denied'), $denied->message());
    }

    public function test_limit_plan_inspection_allows_below_limit_and_denies_at_limit(): void
    {
        $this->syncAndUsePlan('pro');

        PlansUsageResolverFake::$usage['menus'] = 4;

        $allowed = Plan::inspect('menus');

        $this->assertTrue($allowed->allowed());
        $this->assertSame(4, $allowed->used());
        $this->assertSame(5, $allowed->limit());
        $this->assertSame(1, $allowed->remaining());
        $this->assertSame(80, $allowed->percentage());

        PlansUsageResolverFake::$usage['menus'] = 5;

        $denied = Plan::inspect('menus');

        $this->assertTrue($denied->denied());
        $this->assertSame(__('plans::messages.limit_reached', ['used' => 5, 'limit' => 5]), $denied->message());
    }

    public function test_unlimited_limit_allows_any_usage(): void
    {
        $this->syncAndUsePlan('agency');

        $menus = PlanKey::query()->where('key', 'menus')->firstOrFail();

        PlanEntitlement::query()
            ->where('plan_id', PlansBillingResolverFake::$plan?->getKey())
            ->where('plan_key_id', $menus->getKey())
            ->update(['value' => '-1']);

        PlansUsageResolverFake::$usage['menus'] = 999;

        $result = Plan::inspect('menus');

        $this->assertTrue($result->allowed());
        $this->assertSame(999, $result->used());
        $this->assertNull($result->limit());
        $this->assertNull($result->remaining());
    }

    public function test_metered_plan_inspection_stores_current_month_period(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-15 12:00:00'));
        $this->syncAndUsePlan('starter');

        PlansUsageResolverFake::$usage['emails.monthly'] = 25;

        $result = Plan::inspect('emails.monthly');

        $this->assertTrue($result->allowed());
        $this->assertSame(25, $result->used());
        $this->assertInstanceOf(UsagePeriod::class, PlansUsageResolverFake::$lastPeriodByKey['emails.monthly']);

        $usage = EntitlementUsage::query()->firstOrFail();

        $this->assertSame(25, $usage->used);
        $this->assertSame('2026-06-01 00:00:00', $usage->period_started_at?->toDateTimeString());
        $this->assertSame('2026-06-30 23:59:59', $usage->period_ends_at?->toDateTimeString());
    }

    #[DataProvider('writeAllowedStatuses')]
    public function test_active_and_trialing_allow_write(string $status): void
    {
        $this->syncAndUsePlan('pro');
        PlansBillingResolverFake::$status = $status;
        PlansUsageResolverFake::$usage['menus'] = 1;

        $this->assertTrue(Plan::inspect('menus')->allowed());
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function writeAllowedStatuses(): array
    {
        return [
            ['active'],
            ['trialing'],
        ];
    }

    public function test_grace_blocks_write_but_allows_read(): void
    {
        $this->syncAndUsePlan('pro');
        PlansBillingResolverFake::$status = 'grace';
        PlansUsageResolverFake::$usage['menus'] = 1;

        $write = Plan::inspect('menus');
        $read = Plan::inspect('menus', mode: 'read');

        $this->assertTrue($write->denied());
        $this->assertSame(__('plans::messages.write_actions_disabled_in_grace'), $write->message());
        $this->assertTrue($read->allowed());
    }

    #[DataProvider('blockedStatuses')]
    public function test_expired_and_cancelled_block_all_access(string $status): void
    {
        $this->syncAndUsePlan('pro');
        PlansBillingResolverFake::$status = $status;

        $write = Plan::inspect('menus');
        $read = Plan::inspect('menus', mode: 'read');

        $this->assertTrue($write->denied());
        $this->assertTrue($read->denied());
        $this->assertSame(__('plans::messages.subscription_not_active'), $read->message());
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function blockedStatuses(): array
    {
        return [
            ['expired'],
            ['cancelled'],
        ];
    }

    public function test_facade_api_returns_expected_values(): void
    {
        $this->syncAndUsePlan('pro');
        PlansUsageResolverFake::$usage['menus'] = 2;

        $this->assertTrue(Plan::allows('menus'));
        $this->assertFalse(Plan::denies('menus'));
        $this->assertSame(2, Plan::usage('menus'));
        $this->assertSame(5, Plan::limit('menus'));
        $this->assertSame(3, Plan::remaining('menus'));
        $this->assertSame(40, Plan::percentage('menus'));
        $this->assertInstanceOf(PlanResult::class, Plan::sync('menus'));
        $this->assertCount(5, Plan::syncAll());
    }

    public function test_resolvers_are_resolved_from_configured_app_resolvers(): void
    {
        $this->assertInstanceOf(PlansCurrentTeamResolverFake::class, app(CurrentTeamResolverContract::class));
        $this->assertInstanceOf(PlansBillingResolverFake::class, app(BillingResolverContract::class));
        $this->assertInstanceOf(PlansUsageResolverFake::class, app(PlanUsageResolverContract::class));
    }

    public function test_translations_are_loaded_for_messages_keys_and_plans(): void
    {
        $this->syncAndUsePlan('pro');

        app()->setLocale('hr');

        $menus = PlanKey::query()->where('key', 'menus')->firstOrFail();
        $pro = SubscriptionPlan::query()->where('slug', 'pro')->firstOrFail();

        $this->assertSame('Meniji', $menus->translatedName());
        $this->assertSame('Pro', $pro->translatedName());
        $this->assertNotSame('plans::messages.limit_reached', __('plans::messages.limit_reached', ['used' => 5, 'limit' => 5]));

        app()->setLocale('en');

        $this->assertSame('Menus', $menus->translatedName());
        $this->assertSame('Advanced plan for more menus, languages and professional features.', $pro->translatedDescription());
    }

    public function test_core_code_does_not_contain_forbidden_couplings(): void
    {
        $contents = collect(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(dirname(__DIR__, 2).'/src')))
            ->filter(fn (\SplFileInfo $file): bool => $file->isFile() && $file->getExtension() === 'php')
            ->map(fn (\SplFileInfo $file): string => file_get_contents($file->getPathname()) ?: '')
            ->implode("\n");

        foreach ([
            'Livewire\\',
            'Flux\\',
            'Stripe',
            'Paddle',
            'PayPal',
            'enum ',
            'App\\Models\\Menu',
            'App\\Models\\Team',
        ] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $contents);
        }
    }

    private function syncAndUsePlan(string $slug): void
    {
        Artisan::call('plans:sync');

        PlansBillingResolverFake::$plan = $this->plan($slug);
        PlansBillingResolverFake::$status = 'active';
    }

    private function plan(string $slug): SubscriptionPlan
    {
        return SubscriptionPlan::query()
            ->where('slug', $slug)
            ->firstOrFail();
    }

    private function refreshPackageSchema(): void
    {
        Schema::disableForeignKeyConstraints();

        try {
            foreach ([
                config('plans.tables.entitlement_usages', 'entitlement_usages'),
                config('plans.tables.plan_entitlements', 'plan_entitlements'),
                config('plans.tables.plan_keys', 'plan_keys'),
                config('plans.tables.plans', 'plans'),
            ] as $table) {
                Schema::dropIfExists($table);
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        $migrations = glob(dirname(__DIR__, 2).'/database/migrations/*.php') ?: [];
        sort($migrations);

        foreach ($migrations as $migrationPath) {
            $migration = require $migrationPath;
            $migration->up();
        }
    }
}

final class PlansTestOwner extends Model
{
    public $timestamps = false;

    protected $guarded = [];
}

final class PlansCurrentTeamResolverFake implements CurrentTeamResolverContract
{
    public static PlansTestOwner $owner;

    public function current(): Model
    {
        return self::$owner;
    }
}

final class PlansBillingResolverFake implements BillingResolverContract
{
    public static ?SubscriptionPlan $plan = null;

    public static string $status = 'active';

    public function plan(): ?SubscriptionPlan
    {
        return self::$plan;
    }

    public function status(): string
    {
        return self::$status;
    }

    public function canAccessApplication(): bool
    {
        return (bool) config("plans.access.statuses.{$this->status()}.can_access_application", false);
    }

    public function canPerformWriteActions(): bool
    {
        return (bool) config("plans.access.statuses.{$this->status()}.can_perform_write_actions", false);
    }
}

final class PlansUsageResolverFake implements PlanUsageResolverContract
{
    /** @var array<string, int> */
    public static array $usage = [];

    /** @var array<string, UsagePeriod|null> */
    public static array $lastPeriodByKey = [];

    public function used(Model $owner, PlanKey $key, ?UsagePeriod $period = null): int
    {
        self::$lastPeriodByKey[$key->key] = $period;

        return self::$usage[$key->key] ?? 0;
    }
}
