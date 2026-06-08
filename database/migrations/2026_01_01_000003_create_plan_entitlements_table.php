<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('plans.tables.plan_entitlements', 'plan_entitlements'), function (Blueprint $table): void {
            $table->id();
            $table->foreignId('plan_id')
                ->constrained(config('plans.tables.plans', 'plans'))
                ->cascadeOnDelete();
            $table->foreignId('plan_key_id')
                ->constrained(config('plans.tables.plan_keys', 'plan_keys'))
                ->cascadeOnDelete();
            $table->string('value');
            $table->timestamps();

            $table->unique(['plan_id', 'plan_key_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('plans.tables.plan_entitlements', 'plan_entitlements'));
    }
};
