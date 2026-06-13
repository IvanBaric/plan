<x-dynamic-component :component="$layout" :title="$plan->translatedName()">
    <div class="mx-auto w-full max-w-5xl space-y-8 p-4 sm:p-6 lg:p-8">
        <header class="flex flex-col gap-4 border-b border-zinc-200/70 pb-6 dark:border-zinc-800/70 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">{{ __('plans::admin.plans.title') }}</p>
                <h1 class="mt-1 text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ $plan->translatedName() }}</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-zinc-600 dark:text-zinc-400">{{ $plan->translatedDescription() }}</p>
            </div>

            <a href="{{ route($routeNamePrefix.'index') }}" class="inline-flex items-center justify-center rounded-lg border border-zinc-200 px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-900">
                {{ __('plans::admin.actions.back_to_plans') }}
            </a>
        </header>

        @php
            $prices = is_array($plan->prices) ? $plan->prices : [];
            $monthly = data_get($prices, 'monthly.amount');
            $yearly = data_get($prices, 'yearly.amount');
            $currency = data_get($prices, 'monthly.currency', data_get($prices, 'yearly.currency', 'EUR'));
        @endphp

        <section class="grid gap-3 sm:grid-cols-3">
            <div class="rounded-lg bg-white p-4 shadow-sm ring-1 ring-zinc-950/5 dark:bg-zinc-950 dark:ring-white/10">
                <p class="text-xs text-zinc-500">{{ __('plans::admin.labels.monthly_price') }}</p>
                <p class="mt-1 text-2xl font-semibold text-zinc-950 dark:text-white">{{ $monthly ?? '-' }} {{ $currency }}</p>
            </div>
            <div class="rounded-lg bg-white p-4 shadow-sm ring-1 ring-zinc-950/5 dark:bg-zinc-950 dark:ring-white/10">
                <p class="text-xs text-zinc-500">{{ __('plans::admin.labels.yearly_price') }}</p>
                <p class="mt-1 text-2xl font-semibold text-zinc-950 dark:text-white">{{ $yearly ?? '-' }} {{ $currency }}</p>
            </div>
            <div class="rounded-lg bg-white p-4 shadow-sm ring-1 ring-zinc-950/5 dark:bg-zinc-950 dark:ring-white/10">
                <p class="text-xs text-zinc-500">{{ __('plans::admin.labels.status') }}</p>
                <p class="mt-1 text-2xl font-semibold text-zinc-950 dark:text-white">{{ $plan->is_active ? __('plans::admin.labels.active') : __('plans::admin.labels.inactive') }}</p>
            </div>
        </section>

        <section class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-zinc-950/5 dark:bg-zinc-950 dark:ring-white/10">
            <div class="grid gap-4 border-b border-zinc-100 bg-zinc-50 px-5 py-3 text-xs font-semibold uppercase tracking-[0.12em] text-zinc-500 dark:border-zinc-800 dark:bg-zinc-900 sm:grid-cols-[minmax(14rem,1fr)_minmax(8rem,0.5fr)_minmax(8rem,0.5fr)]">
                <div>{{ __('plans::admin.labels.key') }}</div>
                <div>{{ __('plans::admin.labels.value') }}</div>
                <div>{{ __('plans::admin.labels.type') }}</div>
            </div>

            <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @foreach ($plan->entitlements as $entitlement)
                    @php($key = $entitlement->key)
                    <div class="grid gap-2 px-5 py-3 text-sm sm:grid-cols-[minmax(14rem,1fr)_minmax(8rem,0.5fr)_minmax(8rem,0.5fr)] sm:gap-4">
                        <div>
                            <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $key?->translatedName() ?? $entitlement->plan_key_id }}</p>
                            @if ($key?->translatedDescription())
                                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $key->translatedDescription() }}</p>
                            @endif
                        </div>
                        <div class="text-zinc-600 dark:text-zinc-300">
                            @if ($key?->isBoolean())
                                {{ $entitlement->value === '1' ? __('plans::admin.labels.enabled') : __('plans::admin.labels.disabled') }}
                            @elseif (in_array($entitlement->value, ['-1', 'unlimited', '*'], true))
                                {{ __('plans::admin.labels.unlimited') }}
                            @else
                                {{ $entitlement->value }}
                            @endif
                        </div>
                        <div class="text-zinc-600 dark:text-zinc-300">{{ $key?->type ?? '-' }}</div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
</x-dynamic-component>
