# IvanBaric Plans

SaaS plans, plan keys, entitlements, synced usage, and metered usage checks for Laravel 13 applications.

The package has no Livewire components, no Flux UI components, no payment provider logic, no billing emails, no plan key Enums, and no hardcoded application models in core code.

## Installation

```bash
composer require ivanbaric/plans
php artisan plans:install --migrate --sync
```

## Publish config

```bash
php artisan vendor:publish --tag=plans-config
```

The config lives at `config/plans.php`.

## Publish migrations

```bash
php artisan vendor:publish --tag=plans-migrations
```

Then run:

```bash
php artisan migrate
```

## Publish resolvers

```bash
php artisan vendor:publish --tag=plans-resolvers
```

Resolver stubs are published to `app/Resolvers`:

```text
app/Resolvers/CurrentTeamResolver.php
app/Resolvers/BillingResolver.php
app/Resolvers/PlanUsageResolver.php
```

## Publish translations

```bash
php artisan vendor:publish --tag=plans-translations
```

Override location:

```text
lang/vendor/plans/hr/messages.php
lang/vendor/plans/en/messages.php
lang/vendor/plans/hr/keys.php
lang/vendor/plans/en/keys.php
lang/vendor/plans/hr/plans.php
lang/vendor/plans/en/plans.php
```

Every user-visible package message goes through Laravel's `__()` helper:

```php
__('plans::messages.limit_reached', [
    'used' => 5,
    'limit' => 5,
]);

__('plans::keys.menus.name');

__('plans::plans.pro.description');
```

## Run plans sync

```bash
php artisan plans:sync
```

Default sync is production-safe: it creates missing plan records, keys and entitlements, but does not overwrite existing runtime values.

Overwrite existing definitions from config explicitly:

```bash
php artisan plans:sync --force
```

Deactivate plans and keys that no longer exist in config:

```bash
php artisan plans:sync --deactivate-missing --force
```

`plans:sync` fills:

```text
plans
plan_keys
plan_entitlements
```

It does not fill `entitlement_usages`. Usage rows are synced when the application calls `Plan::inspect()`, `Plan::sync()`, or `Plan::syncAll()`.

## Config structure

`config/plans.php` defines:

```text
tables
models
resolvers
access statuses
sync behavior
translation keys
unlimited values
plan key types
periods
keys
plans
```

Config is definition input. The database is the runtime source of truth for plans, keys, and entitlements after `plans:sync`.

## Plan keys

Plan keys are strings, not Enums:

```php
'keys' => [
    'menus' => [
        'name_key' => 'plans::keys.menus.name',
        'description_key' => 'plans::keys.menus.description',
        'type' => 'limit',
        'is_active' => true,
    ],
]
```

Dots are valid:

```php
'qr.svg_download' => [
    'name_key' => 'plans::keys.qr_svg_download.name',
    'description_key' => 'plans::keys.qr_svg_download.description',
    'type' => 'boolean',
]
```

## Plan types

Supported types:

```text
boolean: feature is on or off
limit: non-periodic usage limit
metered: periodic usage limit
```

Unlimited values are configured in `plans.unlimited_values`, for example `-1`, `unlimited`, and `*`.

## Boolean features

```php
Plan::inspect('qr.svg_download');
Plan::allows('qr.svg_download');
Plan::denies('qr.svg_download');
```

Boolean features do not use usage counting.

## Limit features

```php
$result = Plan::inspect('menus');

$result->allowed();
$result->used();
$result->limit();
$result->remaining();
$result->percentage();
$result->message();
```

For limits, `Plan::inspect()` syncs real application usage through `PlanUsageResolver` by default.

## Metered features

```php
$result = Plan::inspect('emails.monthly');
```

Metered keys use a period. The default `emails.monthly` key uses the current calendar month and stores:

```text
period_started_at
period_ends_at
synced_at
used
```

## BillingResolver setup

`BillingResolver` owns billing state resolution, but not payment processing.

```php
use IvanBaric\Plans\Contracts\BillingResolver as BillingResolverContract;
use IvanBaric\Plans\Models\SubscriptionPlan;

final readonly class BillingResolver implements BillingResolverContract
{
    public function plan(): ?SubscriptionPlan
    {
        // Return the SubscriptionPlan model for the current owner.
    }

    public function status(): string
    {
        // Return active, trialing, grace, expired, cancelled, or none.
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
```

## CurrentTeamResolver setup

`CurrentTeamResolver` returns the current owner model. In a team SaaS app this is usually the current team, but the package core does not know about any concrete team class.

```php
use Illuminate\Database\Eloquent\Model;
use IvanBaric\Plans\Contracts\CurrentTeamResolver as CurrentTeamResolverContract;

final readonly class CurrentTeamResolver implements CurrentTeamResolverContract
{
    public function current(): Model
    {
        return auth()->user()->currentTeam;
    }
}
```

## PlanUsageResolver setup

`PlanUsageResolver` is application code. This is where the app counts its own models.

```php
use Illuminate\Database\Eloquent\Model;
use IvanBaric\Plans\Contracts\PlanUsageResolver as PlanUsageResolverContract;
use IvanBaric\Plans\Data\UsagePeriod;
use IvanBaric\Plans\Models\PlanKey;

final readonly class PlanUsageResolver implements PlanUsageResolverContract
{
    public function used(Model $owner, PlanKey $key, ?UsagePeriod $period = null): int
    {
        return match ($key->key) {
            'menus' => 0,
            'emails.monthly' => 0,
            default => 0,
        };
    }
}
```

## Livewire 4 usage

Frontend state is only UX. Backend checks in action methods are mandatory.

```php
use App\Models\Menu;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use IvanBaric\Plans\Facades\Plan;

public function save(): void
{
    $this->validate([
        'name' => ['required', 'string', 'max:255'],
    ]);

    $permission = Gate::inspect('create', Menu::class);

    if (! $permission->allowed()) {
        Flux::toast(
            text: $permission->message() ?? __('app.messages.permission_denied'),
            variant: 'danger',
        );

        return;
    }

    $plan = Plan::inspect('menus');

    if ($plan->denied()) {
        Flux::toast(
            text: $plan->message(),
            variant: 'danger',
        );

        return;
    }

    Menu::query()->create([
        'team_id' => auth()->user()->currentTeam->id,
        'name' => $this->name,
    ]);

    Flux::toast(
        text: __('app.messages.menu_created'),
        variant: 'success',
    );
}
```

## Flux UI example

The package does not ship Flux UI components. Flux can be used in the consuming app documentation or views.

```php
public mixed $menuUsage = null;

public function mount(): void
{
    $this->menuUsage = Plan::inspect('menus', mode: 'read');
}
```

```blade
<flux:card>
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <flux:heading>{{ __('plans::keys.menus.name') }}</flux:heading>

            <flux:text>
                {{ $menuUsage->used() }} / {{ $menuUsage->limit() ?? __('plans::messages.unlimited') }}
            </flux:text>
        </div>

        <flux:progress value="{{ $menuUsage->percentage() }}" />

        @if ($menuUsage->denied())
            <flux:text class="text-red-600">
                {{ $menuUsage->message() }}
            </flux:text>
        @else
            <flux:text>
                {{ __('app.messages.remaining_menus', ['count' => $menuUsage->remaining()]) }}
            </flux:text>
        @endif
    </div>
</flux:card>
```

## Gate + Plan example

These are separate checks:

```php
Gate::inspect('create', Menu::class);
Plan::inspect('menus');
```

`Gate::inspect()` asks whether this user may perform an action. `Plan::inspect()` asks whether the SaaS plan allows the feature or limit.

Recommended action order:

```text
1. validation
2. Gate or Policy permission
3. Plan::inspect()
4. create or update action
5. toast or response
```

## Registration flow

The package does not own subscription lifecycle. A consuming app can assign the default plan after owner creation:

```php
use Illuminate\Support\Facades\DB;
use IvanBaric\Plans\Models\SubscriptionPlan;

DB::transaction(function () use ($team): void {
    $plan = SubscriptionPlan::query()
        ->where('slug', config('plans.default_plan'))
        ->firstOrFail();

    $team->subscription()->create([
        'plan_id' => $plan->id,
        'status' => 'trialing',
        'trial_ends_at' => now()->addDays(config('plans.trial_days')),
        'active_until' => now()->addDays(config('plans.trial_days')),
    ]);
});
```

## Trial and grace

Trial and grace are billing statuses, not plan definitions.

```text
active: write allowed
trialing: write allowed
grace: read allowed, write denied
expired: read and write denied
cancelled: read and write denied
none: read and write denied
```

The package reads those decisions through `BillingResolver`.

## Commands

```bash
php artisan plans:install --migrate --sync
php artisan plans:sync
php artisan plans:sync --deactivate-missing
php artisan vendor:publish --tag=plans-config
php artisan vendor:publish --tag=plans-migrations
php artisan vendor:publish --tag=plans-resolvers
php artisan vendor:publish --tag=plans-translations
```

## Testing

The package should be tested for:

```text
plans:sync creates plan_keys, plans, and plan_entitlements
plans:sync is idempotent
plans:sync --deactivate-missing deactivates removed plans and keys
Plan::inspect handles boolean, limit, unlimited, and metered keys
Billing statuses enforce read/write behavior
Facade methods return expected scalar values
Resolvers are resolved through package contracts
Translations are loaded and publishable
Core code has no Livewire, Flux, payment provider, email, Enum, Team, or application model coupling
```
