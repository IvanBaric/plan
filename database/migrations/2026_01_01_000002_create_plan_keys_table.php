<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('plans.tables.plan_keys', 'plan_keys'), function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->string('name_key')->nullable();
            $table->string('description_key')->nullable();
            $table->string('type');
            $table->string('period')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('plans.tables.plan_keys', 'plan_keys'));
    }
};
