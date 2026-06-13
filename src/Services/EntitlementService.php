<?php

declare(strict_types=1);

namespace IvanBaric\Plans\Services;

use Illuminate\Database\Eloquent\Model;
use IvanBaric\Plans\Contracts\CurrentPlanResolver;
use IvanBaric\Plans\Contracts\PlanRepository;
use IvanBaric\Plans\Contracts\UsageCounter;
use IvanBaric\Plans\Enums\FeatureKey;
use IvanBaric\Plans\Exceptions\FeatureNotAvailableException;
use IvanBaric\Plans\Exceptions\PlanLimitExceededException;

final readonly class EntitlementService
{
    public function __construct(
        private CurrentPlanResolver $currentPlanResolver,
        private PlanRepository $plans,
        private UsageCounter $usage,
    ) {}

    public function enabled(Model $billable, string $feature): bool
    {
        $value = $this->value($billable, $feature);

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value > 0;
        }

        return $value !== null;
    }

    public function value(Model $billable, string $feature): mixed
    {
        $planCode = $this->currentPlanResolver->resolveForBillable($billable);

        if ($planCode === null) {
            return null;
        }

        return $this->plans->find($planCode)?->value($feature);
    }

    public function limit(Model $billable, string $feature): ?int
    {
        $value = $this->value($billable, $feature);

        if ($this->isUnlimited($value)) {
            return null;
        }

        return is_int($value) ? $value : 0;
    }

    public function remaining(Model $billable, string $feature, int $used): ?int
    {
        $limit = $this->limit($billable, $feature);

        return $limit === null ? null : max(0, $limit - $used);
    }

    public function assertFeatureEnabled(Model $billable, string $feature): void
    {
        if (! $this->enabled($billable, $feature)) {
            throw FeatureNotAvailableException::forFeature($feature);
        }
    }

    public function assertCanCreatePublicQrMenu(Model $billable): void
    {
        $this->assertWithinLimit($billable, FeatureKey::PublicQrMenusLimit, $this->usage->countPublicQrMenus($billable));
    }

    public function assertCanCreateMenuItem(Model $billable): void
    {
        $this->assertWithinLimit($billable, FeatureKey::MenuItemsLimit, $this->usage->countMenuItems($billable));
    }

    public function assertCanAddLanguage(Model $billable): void
    {
        $this->assertWithinLimit($billable, FeatureKey::LanguagesLimit, $this->usage->countLanguages($billable));
    }

    public function assertCanUploadItemImage(Model $billable): void
    {
        $this->assertWithinLimit($billable, FeatureKey::ItemImagesLimit, $this->usage->countItemImages($billable));
    }

    public function assertCanInviteTeamMember(Model $billable): void
    {
        $this->assertWithinLimit($billable, FeatureKey::TeamMembersLimit, $this->usage->countTeamMembers($billable));
    }

    private function assertWithinLimit(Model $billable, string $feature, int $used): void
    {
        $limit = $this->limit($billable, $feature);

        if ($limit === null) {
            return;
        }

        if ($used >= $limit) {
            throw PlanLimitExceededException::forFeature($feature, $limit, $used);
        }
    }

    private function isUnlimited(mixed $value): bool
    {
        $unlimited = (array) config('plans.unlimited_values', []);
        $stringValues = array_map(static fn (mixed $item): string => (string) $item, $unlimited);

        return in_array($value, $unlimited, true)
            || in_array((string) $value, $stringValues, true);
    }
}
