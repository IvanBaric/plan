<?php

declare(strict_types=1);

namespace IvanBaric\Plans\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use IvanBaric\Plans\Contracts\UsageCounter;

final class EloquentUsageCounter implements UsageCounter
{
    public function countPublicQrMenus(Model $billable): int
    {
        $relation = $this->relation($billable, (string) config('plans.usage.relationships.public_qr_menus', 'qrMenus'));

        if ($relation instanceof Relation) {
            $query = $relation->getQuery();

            if (Schema::hasColumn($relation->getRelated()->getTable(), 'is_visible')) {
                $query->where('is_visible', true);
            }

            return (int) $query->count();
        }

        return $this->countTableByBillable('qr_menus', $billable, true);
    }

    public function countMenuItems(Model $billable): int
    {
        if (class_exists(\App\Models\QrMenuItem::class) && Schema::hasTable('qr_menu_items')) {
            return (int) \App\Models\QrMenuItem::query()
                ->where('team_id', $billable->getKey())
                ->count();
        }

        return $this->countTableByBillable('qr_menu_items', $billable);
    }

    public function countLanguages(Model $billable): int
    {
        $languages = collect();

        if (is_string($billable->getAttribute('input_language')) && $billable->getAttribute('input_language') !== '') {
            $languages->push($billable->getAttribute('input_language'));
        }

        if (Schema::hasTable('qr_menus') && Schema::hasColumn('qr_menus', 'languages')) {
            DB::table('qr_menus')
                ->where('team_id', $billable->getKey())
                ->pluck('languages')
                ->each(function ($stored) use ($languages): void {
                    $decoded = is_string($stored) ? json_decode($stored, true) : null;

                    foreach (is_array($decoded) ? $decoded : [] as $language) {
                        if (is_string($language) && $language !== '') {
                            $languages->push($language);
                        }
                    }
                });
        }

        return $languages->unique()->count();
    }

    public function countItemImages(Model $billable): int
    {
        if (! Schema::hasTable('media') || ! Schema::hasTable('qr_menu_items')) {
            return 0;
        }

        $itemIds = DB::table('qr_menu_items')
            ->where('team_id', $billable->getKey())
            ->pluck('id');

        if ($itemIds->isEmpty()) {
            return 0;
        }

        return (int) DB::table('media')
            ->whereIn('model_id', $itemIds)
            ->where('model_type', \App\Models\QrMenuItem::class)
            ->count();
    }

    public function countTeamMembers(Model $billable): int
    {
        $relation = $this->relation($billable, (string) config('plans.usage.relationships.memberships', 'memberships'));

        if ($relation instanceof Relation) {
            return (int) $relation->getQuery()->count();
        }

        return $this->countTableByBillable('team_memberships', $billable);
    }

    private function relation(Model $billable, string $name): ?Relation
    {
        if ($name === '' || ! method_exists($billable, $name)) {
            return null;
        }

        $relation = $billable->{$name}();

        return $relation instanceof Relation ? $relation : null;
    }

    private function countTableByBillable(string $table, Model $billable, bool $publicOnly = false): int
    {
        if (! Schema::hasTable($table)) {
            return 0;
        }

        $column = Schema::hasColumn($table, 'team_id') ? 'team_id' : null;

        if ($column === null) {
            return 0;
        }

        $query = DB::table($table)->where($column, $billable->getKey());

        if ($publicOnly && Schema::hasColumn($table, 'is_visible')) {
            $query->where('is_visible', true);
        }

        return (int) $query->count();
    }
}
