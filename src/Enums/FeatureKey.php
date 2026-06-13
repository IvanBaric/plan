<?php

declare(strict_types=1);

namespace IvanBaric\Plans\Enums;

final class FeatureKey
{
    public const PublicQrMenusLimit = 'public_qr_menus_limit';
    public const MenuItemsLimit = 'menu_items_limit';
    public const LanguagesLimit = 'languages_limit';
    public const ItemImagesLimit = 'item_images_limit';
    public const SelfTranslations = 'self_translations';
    public const AiTranslations = 'ai_translations';
    public const BasicTemplates = 'basic_templates';
    public const AllTemplates = 'all_templates';
    public const PngQrDownload = 'png_qr_download';
    public const SvgQrDownload = 'svg_qr_download';
    public const PrintQrOption = 'print_qr_option';
    public const ShareQrOption = 'share_qr_option';
    public const MobilePublicView = 'mobile_public_view';
    public const LegalPages = 'legal_pages';
    public const LegalPagesPerBusiness = 'legal_pages_per_business';
    public const CookieBanner = 'cookie_banner';
    public const BasicAnalytics = 'basic_analytics';
    public const AnalyticsBasic = 'analytics_basic';
    public const AnalyticsRetentionDays = 'analytics_retention_days';
    public const AnalyticsMenuBreakdown = 'analytics_menu_breakdown';
    public const AnalyticsLanguageBreakdown = 'analytics_language_breakdown';
    public const AnalyticsDeviceBreakdown = 'analytics_device_breakdown';
    public const AnalyticsHourlyBreakdown = 'analytics_hourly_breakdown';
    public const AnalyticsLocationBreakdown = 'analytics_location_breakdown';
    public const AnalyticsItemViews = 'analytics_item_views';
    public const AnalyticsTrends = 'analytics_trends';
    public const PriceVariants = 'price_variants';
    public const FeaturedItems = 'featured_items';
    public const MenuScheduling = 'menu_scheduling';
    public const Allergens = 'allergens';
    public const Ingredients = 'ingredients';
    public const Tags = 'tags';
    public const DesignerHandoff = 'designer_handoff';
    public const ArchiveRestore = 'archive_restore';
    public const CustomDomain = 'custom_domain';
    public const CustomDomainManualActivation = 'custom_domain_manual_activation';
    public const TeamMembersLimit = 'team_members_limit';
    public const RolesAndPermissions = 'roles_and_permissions';
    public const AuditLog = 'audit_log';
    public const WhiteLabel = 'white_label';
    public const SetupHelp = 'setup_help';
    public const SupportLevel = 'support_level';
}
