<?php

declare(strict_types=1);

namespace IvanBaric\Plans\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

final class SubscriptionPlan extends Model
{
    protected $fillable = [
        'uuid',
        'slug',
        'name_key',
        'description_key',
        'is_active',
        'is_public',
        'sort_order',
        'prices',
    ];

    protected $casts = [
        'prices' => 'array',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function getTable(): string
    {
        return config('plans.tables.plans', 'plans');
    }

    /**
     * @return HasMany<PlanEntitlement, $this>
     */
    public function entitlements(): HasMany
    {
        /** @var class-string<PlanEntitlement> $model */
        $model = config('plans.models.plan_entitlement', PlanEntitlement::class);

        return $this->hasMany($model, 'plan_id');
    }

    public function translatedName(): string
    {
        return __($this->name_key ?? "plans::plans.{$this->slug}.name");
    }

    public function translatedDescription(): ?string
    {
        if ($this->description_key === null) {
            return null;
        }

        return __($this->description_key);
    }

    /**
     * @return Builder<SubscriptionPlan>
     */
    public static function query(): Builder
    {
        return parent::query();
    }

    protected static function booted(): void
    {
        self::creating(function (SubscriptionPlan $plan): void {
            if (! is_string($plan->uuid) || $plan->uuid === '') {
                $plan->uuid = (string) Str::uuid();
            }
        });
    }
}
