<?php

declare(strict_types=1);

return [
    'title' => 'Plans',

    'plans' => [
        'title' => 'Plan definitions',
        'description' => 'Read-only overview of plans, keys and entitlements synced from config/plans.php.',
    ],

    'empty' => [
        'title' => 'No plans found',
        'description' => 'Run the plans seeder or plans:sync command after the migrations are deployed.',
    ],

    'actions' => [
        'details' => 'View details',
        'back_to_plans' => 'Back to plans',
    ],

    'labels' => [
        'active' => 'Active',
        'disabled' => 'Disabled',
        'enabled' => 'Enabled',
        'inactive' => 'Inactive',
        'initial_sync' => 'Initial sync',
        'key' => 'Key',
        'monthly_price' => 'Monthly price',
        'fair_use' => 'Fair use',
        'status' => 'Status',
        'type' => 'Type',
        'unlimited' => 'Unlimited',
        'value' => 'Value',
        'yearly_price' => 'Yearly price',
    ],
];
