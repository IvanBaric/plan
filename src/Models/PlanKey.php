<?php

declare(strict_types=1);

namespace IvanBaric\Plans\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class PlanKey extends Model
{
    protected $fillable = [
        'key',
        'name_key',
        'description_key',
        'type',
        'period',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getTable(): string
    {
        return config('plans.tables.plan_keys', 'plan_keys');
    }

    /**
     * @return HasMany<PlanEntitlement, $this>
     */
    public function entitlements(): HasMany
    {
        /** @var class-string<PlanEntitlement> $model */
        $model = config('plans.models.plan_entitlement', PlanEntitlement::class);

        return $this->hasMany($model, 'plan_key_id');
    }

    public function translatedName(): string
    {
        $fallbackKey = str_replace('.', '_', $this->key);

        return __($this->name_key ?? "plans::keys.{$fallbackKey}.name");
    }

    public function translatedDescription(): ?string
    {
        if ($this->description_key === null) {
            return null;
        }

        return __($this->description_key);
    }

    public function usesUsage(): bool
    {
        return (bool) config("plans.types.{$this->type}.uses_usage", false);
    }

    public function isBoolean(): bool
    {
        return $this->type === 'boolean';
    }

    public function isLimit(): bool
    {
        return $this->type === 'limit';
    }

    public function isMetered(): bool
    {
        return $this->type === 'metered';
    }

    /**
     * @return Builder<PlanKey>
     */
    public static function query(): Builder
    {
        return parent::query();
    }
}
