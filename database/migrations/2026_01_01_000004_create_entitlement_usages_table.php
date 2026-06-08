<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('plans.tables.entitlement_usages', 'entitlement_usages'), function (Blueprint $table): void {
            $table->id();
            $table->morphs('owner');
            $table->foreignId('plan_key_id')
                ->constrained(config('plans.tables.plan_keys', 'plan_keys'))
                ->cascadeOnDelete();
            $table->unsignedInteger('used')->default(0);
            $table->timestamp('period_started_at')->nullable();
            $table->timestamp('period_ends_at')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['owner_type', 'owner_id', 'plan_key_id', 'period_started_at', 'period_ends_at'],
                'entitlement_usage_owner_key_period_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('plans.tables.entitlement_usages', 'entitlement_usages'));
    }
};
