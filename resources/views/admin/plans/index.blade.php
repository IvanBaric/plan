<x-dynamic-component :component="$layout" :title="__('plans::admin.plans.title')">
    <div class="mx-auto w-full max-w-7xl space-y-8 p-4 sm:p-6 lg:p-8">
        <header class="flex flex-col gap-4 border-b border-zinc-200/70 pb-6 dark:border-zinc-800/70 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">{{ __('plans::admin.title') }}</p>
                <h1 class="mt-1 text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ __('plans::admin.plans.title') }}</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-zinc-600 dark:text-zinc-400">{{ __('plans::admin.plans.description') }}</p>
            </div>

            <div class="rounded-lg bg-zinc-50 px-4 py-3 text-sm text-zinc-600 ring-1 ring-zinc-950/5 dark:bg-zinc-950 dark:text-zinc-300 dark:ring-white/10">
                <span class="font-medium text-zinc-900 dark:text-white">{{ __('plans::admin.labels.initial_sync') }}</span>
                <code class="ml-2 rounded bg-white px-2 py-1 text-xs dark:bg-zinc-900">php artisan db:seed --class=PlansSeeder</code>
            </div>
        </header>

        @if ($plans->isEmpty())
            <section class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-zinc-950/5 dark:bg-zinc-950 dark:ring-white/10">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">{{ __('plans::admin.empty.title') }}</h2>
                <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-400">{{ __('plans::admin.empty.description') }}</p>
            </section>
        @else
            <section class="grid gap-4 lg:grid-cols-3">
                @foreach ($plans as $plan)
                    @php
                        $prices = is_array($plan->prices) ? $plan->prices : [];
                        $monthly = data_get($prices, 'monthly.amount');
                        $yearly = data_get($prices, 'yearly.amount');
                        $currency = data_get($prices, 'monthly.currency', data_get($prices, 'yearly.currency', 'EUR'));
                    @endphp

                    <article class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-zinc-950/5 dark:bg-zinc-950 dark:ring-white/10">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">{{ $plan->translatedName() }}</h2>
                                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $plan->translatedDescription() }}</p>
                            </div>
                            <span class="rounded-md bg-zinc-100 px-2 py-1 text-xs font-medium uppercase tracking-[0.12em] text-zinc-600 dark:bg-zinc-900 dark:text-zinc-300">{{ $plan->slug }}</span>
                        </div>

                        <dl class="mt-5 grid grid-cols-2 gap-3">
                            <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-900">
                                <dt class="text-xs text-zinc-500">{{ __('plans::admin.labels.monthly_price') }}</dt>
                                <dd class="mt-1 text-lg font-semibold text-zinc-950 dark:text-white">{{ $monthly ?? '-' }} {{ $currency }}</dd>
                            </div>
                            <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-900">
                                <dt class="text-xs text-zinc-500">{{ __('plans::admin.labels.yearly_price') }}</dt>
                                <dd class="mt-1 text-lg font-semibold text-zinc-950 dark:text-white">{{ $yearly ?? '-' }} {{ $currency }}</dd>
                            </div>
                        </dl>

                        <div class="mt-5 space-y-2 text-sm">
                            @foreach ($plan->entitlements->take(5) as $entitlement)
                                @php($key = $entitlement->key)
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-zinc-500 dark:text-zinc-400">{{ $key?->translatedName() ?? $entitlement->plan_key_id }}</span>
                                    <span class="font-medium text-zinc-900 dark:text-zinc-100">
                                        @if ($key?->isBoolean())
                                            {{ $entitlement->value === '1' ? __('plans::admin.labels.enabled') : __('plans::admin.labels.disabled') }}
                                        @elseif (in_array($entitlement->value, ['-1', 'unlimited', '*'], true))
                                            {{ __('plans::admin.labels.unlimited') }}
                                        @else
                                            {{ $entitlement->value }}
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-5 border-t border-zinc-100 pt-4 dark:border-zinc-800">
                            <a href="{{ route($routeNamePrefix.'show', $plan->slug) }}" class="inline-flex w-full items-center justify-center rounded-lg border border-zinc-200 px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-900">
                                {{ __('plans::admin.actions.details') }}
                            </a>
                        </div>
                    </article>
                @endforeach
            </section>
        @endif
    </div>
</x-dynamic-component>
