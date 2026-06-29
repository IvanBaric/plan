<?php

declare(strict_types=1);

namespace IvanBaric\Plans\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

final class PlanEntitlement extends Model
{
    protected $fillable = [
        'uuid',
        'plan_id',
        'plan_key_id',
        'value',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $entitlement): void {
            if (Schema::hasColumn($entitlement->getTable(), 'uuid') && blank($entitlement->uuid)) {
                $entitlement->uuid = (string) Str::uuid();
            }
        });
    }

    public function getTable(): string
    {
        return config('plans.tables.plan_entitlements', 'plan_entitlements');
    }

    /**
     * @return BelongsTo<SubscriptionPlan, $this>
     */
    public function plan(): BelongsTo
    {
        /** @var class-string<SubscriptionPlan> $model */
        $model = config('plans.models.plan', SubscriptionPlan::class);

        return $this->belongsTo($model, 'plan_id');
    }

    /**
     * @return BelongsTo<PlanKey, $this>
     */
    public function key(): BelongsTo
    {
        /** @var class-string<PlanKey> $model */
        $model = config('plans.models.plan_key', PlanKey::class);

        return $this->belongsTo($model, 'plan_key_id');
    }

    /**
     * @return Builder<PlanEntitlement>
     */
    public static function query(): Builder
    {
        return parent::query();
    }
}
