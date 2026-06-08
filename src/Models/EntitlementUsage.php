<?php

declare(strict_types=1);

namespace IvanBaric\Plans\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class EntitlementUsage extends Model
{
    protected $fillable = [
        'owner_type',
        'owner_id',
        'plan_key_id',
        'used',
        'period_started_at',
        'period_ends_at',
        'synced_at',
    ];

    protected $casts = [
        'used' => 'integer',
        'period_started_at' => 'datetime',
        'period_ends_at' => 'datetime',
        'synced_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return config('plans.tables.entitlement_usages', 'entitlement_usages');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function owner(): MorphTo
    {
        return $this->morphTo();
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
     * @return Builder<EntitlementUsage>
     */
    public static function query(): Builder
    {
        return parent::query();
    }
}
