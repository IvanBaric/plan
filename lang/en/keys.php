<?php

declare(strict_types=1);

return [
    'menus' => [
        'name' => 'Menus',
        'description' => 'Number of QR menus the team can create.',
    ],

    'items' => [
        'name' => 'Items',
        'description' => 'Number of items across all menus.',
    ],

    'languages' => [
        'name' => 'Languages',
        'description' => 'Number of languages the team can use.',
    ],

    'images' => [
        'name' => 'Images',
        'description' => 'Number of images the team can store.',
    ],

    'qr_svg_download' => [
        'name' => 'SVG QR download',
        'description' => 'Allows QR code download in SVG format.',
    ],

    'ai_translate' => [
        'name' => 'AI translation',
        'description' => 'Allows AI translation usage.',
    ],

    'analytics_advanced' => [
        'name' => 'Advanced analytics',
        'description' => 'Allows access to advanced analytics.',
    ],

    'emails_monthly' => [
        'name' => 'Monthly emails',
        'description' => 'Number of emails the team can send monthly.',
    ],
    'public_qr_menus_limit' => [
        'name' => 'Public QR menus limit',
        'description' => 'Number of public QR menus the business can publish.',
    ],

    'menu_items_limit' => [
        'name' => 'Menu items limit',
        'description' => 'Number of menu items the business can manage.',
    ],

    'languages_limit' => [
        'name' => 'Languages limit',
        'description' => 'Number of menu languages the business can use.',
    ],

    'item_images_limit' => [
        'name' => 'Item images limit',
        'description' => 'Number of item images the business can store.',
    ],

    'self_translations' => [
        'name' => 'Self-managed translations',
        'description' => 'Allows manual menu translations.',
    ],

    'ai_translations' => [
        'name' => 'AI translations',
        'description' => 'Allows AI-assisted translations.',
    ],

    'basic_templates' => [
        'name' => 'Basic templates',
        'description' => 'Allows access to the basic template set.',
    ],

    'all_templates' => [
        'name' => 'All templates',
        'description' => 'Allows access to all public menu templates.',
    ],

    'png_qr_download' => [
        'name' => 'PNG QR download',
        'description' => 'Allows QR code download in PNG format.',
    ],

    'svg_qr_download' => [
        'name' => 'SVG QR download',
        'description' => 'Allows QR code download in SVG format.',
    ],

    'print_qr_option' => [
        'name' => 'Print QR option',
        'description' => 'Allows print-ready QR code output.',
    ],

    'share_qr_option' => [
        'name' => 'Share QR option',
        'description' => 'Allows share-ready QR code output.',
    ],

    'mobile_public_view' => [
        'name' => 'Mobile public view',
        'description' => 'Allows the mobile public menu view.',
    ],

    'legal_pages' => [
        'name' => 'Legal pages',
        'description' => 'Allows legal pages on the public menu.',
    ],

    'legal_pages_per_business' => [
        'name' => 'Legal pages per business',
        'description' => 'Allows business-specific legal pages.',
    ],

    'cookie_banner' => [
        'name' => 'Cookie banner',
        'description' => 'Allows the public cookie banner.',
    ],

    'basic_analytics' => [
        'name' => 'Basic analytics',
        'description' => 'Allows access to basic analytics.',
    ],

    'analytics_basic' => [
        'name' => 'Basic analytics',
        'description' => 'Allows access to basic analytics dashboards.',
    ],

    'analytics_retention_days' => [
        'name' => 'Analytics retention days',
        'description' => 'Number of days analytics data is retained.',
    ],

    'analytics_menu_breakdown' => [
        'name' => 'Menu analytics',
        'description' => 'Allows analytics grouped by menu.',
    ],

    'analytics_language_breakdown' => [
        'name' => 'Language analytics',
        'description' => 'Allows analytics grouped by language.',
    ],

    'analytics_device_breakdown' => [
        'name' => 'Device analytics',
        'description' => 'Allows analytics grouped by device.',
    ],

    'analytics_hourly_breakdown' => [
        'name' => 'Time-of-day analytics',
        'description' => 'Allows analytics grouped by hour.',
    ],

    'analytics_location_breakdown' => [
        'name' => 'Location analytics',
        'description' => 'Allows analytics grouped by location.',
    ],

    'analytics_item_views' => [
        'name' => 'Top viewed items',
        'description' => 'Allows item-view analytics.',
    ],

    'analytics_trends' => [
        'name' => 'Advanced trends',
        'description' => 'Allows advanced analytics trends.',
    ],

    'price_variants' => [
        'name' => 'Price variants',
        'description' => 'Allows item price variants.',
    ],

    'featured_items' => [
        'name' => 'Featured items',
        'description' => 'Allows featured menu items.',
    ],

    'menu_scheduling' => [
        'name' => 'Promo popup',
        'description' => 'Allows menu scheduling and promo popups.',
    ],

    'allergens' => [
        'name' => 'Allergens',
        'description' => 'Allows allergen metadata on items.',
    ],

    'ingredients' => [
        'name' => 'Ingredients',
        'description' => 'Allows ingredient metadata on items.',
    ],

    'tags' => [
        'name' => 'Tags',
        'description' => 'Allows item tags.',
    ],

    'designer_handoff' => [
        'name' => 'Designer handoff',
        'description' => 'Allows designer handoff exports.',
    ],

    'archive_restore' => [
        'name' => 'Archive and restore',
        'description' => 'Allows archive and restore workflows.',
    ],

    'custom_domain' => [
        'name' => 'Custom domain',
        'description' => 'Allows custom public domains.',
    ],

    'custom_domain_manual_activation' => [
        'name' => 'Manual custom domain activation',
        'description' => 'Allows manual custom-domain activation.',
    ],

    'team_members_limit' => [
        'name' => 'Team members limit',
        'description' => 'Number of team members the business can invite.',
    ],

    'roles_and_permissions' => [
        'name' => 'Roles and permissions',
        'description' => 'Allows custom roles and permissions.',
    ],

    'audit_log' => [
        'name' => 'Audit log',
        'description' => 'Allows the audit log.',
    ],

    'white_label' => [
        'name' => 'White-label public view',
        'description' => 'Allows white-label public menu presentation.',
    ],

    'setup_help' => [
        'name' => 'Setup help',
        'description' => 'Includes setup help.',
    ],

    'support_level' => [
        'name' => 'Support level',
        'description' => 'Support tier included with the plan.',
    ],
];
