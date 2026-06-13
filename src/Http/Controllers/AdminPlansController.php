<?php

declare(strict_types=1);

namespace IvanBaric\Plans\Http\Controllers;

use Illuminate\Contracts\View\View;
use IvanBaric\Plans\Models\SubscriptionPlan;

final class AdminPlansController
{
    public function index(): View
    {
        $plans = SubscriptionPlan::query()
            ->with(['entitlements.key'])
            ->orderBy('sort_order')
            ->orderBy('slug')
            ->get();

        return view('plans::admin.plans.index', [
            'layout' => (string) config('plans.admin.layout', 'components.layouts.app'),
            'plans' => $plans,
            'routeNamePrefix' => (string) config('plans.admin.route_name_prefix', 'admin.plans.'),
        ]);
    }

    public function show(string $plan): View
    {
        $plan = SubscriptionPlan::query()
            ->where('slug', $plan)
            ->with(['entitlements.key'])
            ->firstOrFail();

        return view('plans::admin.plans.show', [
            'layout' => (string) config('plans.admin.layout', 'components.layouts.app'),
            'plan' => $plan,
            'routeNamePrefix' => (string) config('plans.admin.route_name_prefix', 'admin.plans.'),
        ]);
    }
}
