<?php

declare(strict_types=1);
use App\Resolvers\BillingResolver;
use App\Resolvers\CurrentTeamResolver;
use App\Resolvers\PlanUsageResolver;
use IvanBaric\Plans\Models\EntitlementUsage;
use IvanBaric\Plans\Models\PlanEntitlement;
use IvanBaric\Plans\Models\PlanKey;
use IvanBaric\Plans\Models\SubscriptionPlan;
use IvanBaric\Plans\Services\DefaultCurrentPlanResolver;

return [

    'enabled' => true,

    'default_plan' => 'starter',

    'trial_days' => 14,

    'grace_days' => 14,

    'tables' => [
        'plans' => 'plans',
        'plan_keys' => 'plan_keys',
        'plan_entitlements' => 'plan_entitlements',
        'entitlement_usages' => 'entitlement_usages',
    ],

    'models' => [
        'plan' => SubscriptionPlan::class,
        'plan_key' => PlanKey::class,
        'plan_entitlement' => PlanEntitlement::class,
        'entitlement_usage' => EntitlementUsage::class,
    ],

    'resolvers' => [
        'current_team' => CurrentTeamResolver::class,
        'billing' => BillingResolver::class,
        'usage' => PlanUsageResolver::class,
    ],

    'billing' => [
        'plan_column' => 'plan_code',
        'resolver' => DefaultCurrentPlanResolver::class,
    ],

    'usage' => [
        'relationships' => [
            'public_qr_menus' => 'qrMenus',
            'memberships' => 'memberships',
        ],
    ],

    'access' => [
        'default_inspection_mode' => 'write',

        'statuses' => [
            'trialing' => [
                'can_access_application' => true,
                'can_perform_write_actions' => true,
            ],

            'active' => [
                'can_access_application' => true,
                'can_perform_write_actions' => true,
            ],

            'grace' => [
                'can_access_application' => true,
                'can_perform_write_actions' => false,
            ],

            'expired' => [
                'can_access_application' => false,
                'can_perform_write_actions' => false,
            ],

            'cancelled' => [
                'can_access_application' => false,
                'can_perform_write_actions' => false,
            ],

            'none' => [
                'can_access_application' => false,
                'can_perform_write_actions' => false,
            ],
        ],
    ],

    'sync' => [
        'overwrite_existing' => false,
        'sync_on_inspect' => true,
        'store_synced_usage' => true,
        'sync_boolean_keys' => false,
        'create_missing_usage_rows' => true,
        'deactivate_missing_keys' => false,
        'deactivate_missing_plans' => false,
        'delete_missing_entitlements' => false,
        'fail_if_plan_entitlement_references_unknown_key' => true,
        'sync_definitions_on_install' => true,
    ],

    'cache' => [
        'enabled' => false,
        'prefix' => 'ivanbaric_plans',
        'ttl_seconds' => 0,
    ],

    'admin' => [
        'enabled' => true,
        'route_prefix' => 'admin/plans',
        'route_name_prefix' => 'admin.plans.',
        'middleware' => ['web', 'auth', 'admin_locale', 'is_superadmin'],
        'layout' => 'components.layouts.app',
    ],

    'translation' => [
        'namespace' => 'plans',

        'messages' => [
            'package_disabled' => 'plans::messages.package_disabled',
            'subscription_not_active' => 'plans::messages.subscription_not_active',
            'write_actions_disabled_in_grace' => 'plans::messages.write_actions_disabled_in_grace',
            'plan_not_found' => 'plans::messages.plan_not_found',
            'key_not_registered' => 'plans::messages.key_not_registered',
            'entitlement_not_available' => 'plans::messages.entitlement_not_available',
            'boolean_denied' => 'plans::messages.boolean_denied',
            'limit_reached' => 'plans::messages.limit_reached',
            'unlimited' => 'plans::messages.unlimited',
        ],
    ],

    'unlimited_values' => [
        -1,
        '-1',
        'unlimited',
        '*',
    ],

    'types' => [
        'boolean' => [
            'uses_usage' => false,
            'periodic' => false,
        ],

        'limit' => [
            'uses_usage' => true,
            'periodic' => false,
        ],

        'metered' => [
            'uses_usage' => true,
            'periodic' => true,
        ],

        'value' => [
            'uses_usage' => false,
            'periodic' => false,
        ],
    ],

    'periods' => [
        'monthly' => [
            'starts' => 'calendar_month',
        ],

        'yearly' => [
            'starts' => 'calendar_year',
        ],
    ],

    'keys' => [
        'menus' => [
            'name_key' => 'plans::keys.menus.name',
            'description_key' => 'plans::keys.menus.description',
            'type' => 'limit',
            'is_active' => true,
        ],

        'items' => [
            'name_key' => 'plans::keys.items.name',
            'description_key' => 'plans::keys.items.description',
            'type' => 'limit',
            'is_active' => true,
        ],

        'languages' => [
            'name_key' => 'plans::keys.languages.name',
            'description_key' => 'plans::keys.languages.description',
            'type' => 'limit',
            'is_active' => true,
        ],

        'images' => [
            'name_key' => 'plans::keys.images.name',
            'description_key' => 'plans::keys.images.description',
            'type' => 'limit',
            'is_active' => true,
        ],

        'qr.svg_download' => [
            'name_key' => 'plans::keys.qr_svg_download.name',
            'description_key' => 'plans::keys.qr_svg_download.description',
            'type' => 'boolean',
            'is_active' => true,
        ],

        'ai.translate' => [
            'name_key' => 'plans::keys.ai_translate.name',
            'description_key' => 'plans::keys.ai_translate.description',
            'type' => 'boolean',
            'is_active' => true,
        ],

        'analytics.advanced' => [
            'name_key' => 'plans::keys.analytics_advanced.name',
            'description_key' => 'plans::keys.analytics_advanced.description',
            'type' => 'boolean',
            'is_active' => true,
        ],

        'emails.monthly' => [
            'name_key' => 'plans::keys.emails_monthly.name',
            'description_key' => 'plans::keys.emails_monthly.description',
            'type' => 'metered',
            'period' => 'monthly',
            'is_active' => true,
        ],
    ],

    'plans' => [
        'starter' => [
            'name_key' => 'plans::plans.starter.name',
            'description_key' => 'plans::plans.starter.description',
            'is_active' => true,
            'is_public' => true,
            'sort_order' => 1,

            'prices' => [
                'monthly' => [
                    'amount' => 0,
                    'currency' => 'EUR',
                ],

                'yearly' => [
                    'amount' => 0,
                    'currency' => 'EUR',
                ],
            ],

            'entitlements' => [
                'menus' => 1,
                'items' => 150,
                'languages' => 2,
                'images' => 20,
                'qr.svg_download' => false,
                'ai.translate' => false,
                'analytics.advanced' => false,
                'emails.monthly' => 100,
            ],
        ],

        'pro' => [
            'name_key' => 'plans::plans.pro.name',
            'description_key' => 'plans::plans.pro.description',
            'is_active' => true,
            'is_public' => true,
            'sort_order' => 2,

            'prices' => [
                'monthly' => [
                    'amount' => 19,
                    'currency' => 'EUR',
                ],

                'yearly' => [
                    'amount' => 190,
                    'currency' => 'EUR',
                ],
            ],

            'entitlements' => [
                'menus' => 5,
                'items' => 1000,
                'languages' => 5,
                'images' => 500,
                'qr.svg_download' => true,
                'ai.translate' => true,
                'analytics.advanced' => true,
                'emails.monthly' => 1000,
            ],
        ],

        'agency' => [
            'name_key' => 'plans::plans.agency.name',
            'description_key' => 'plans::plans.agency.description',
            'is_active' => true,
            'is_public' => true,
            'sort_order' => 3,

            'prices' => [
                'monthly' => [
                    'amount' => 49,
                    'currency' => 'EUR',
                ],

                'yearly' => [
                    'amount' => 490,
                    'currency' => 'EUR',
                ],
            ],

            'entitlements' => [
                'menus' => 25,
                'items' => 5000,
                'languages' => 10,
                'images' => 2500,
                'qr.svg_download' => true,
                'ai.translate' => true,
                'analytics.advanced' => true,
                'emails.monthly' => 10000,
            ],
        ],
    ],
];
