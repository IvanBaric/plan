<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use IvanBaric\Plans\Http\Controllers\AdminPlansController;

$prefix = trim((string) config('plans.admin.route_prefix', 'admin/plans'), '/');
$namePrefix = (string) config('plans.admin.route_name_prefix', 'admin.plans.');
$middleware = (array) config('plans.admin.middleware', ['web', 'auth']);

Route::middleware($middleware)
    ->prefix($prefix)
    ->name($namePrefix)
    ->group(function (): void {
        Route::get('/', [AdminPlansController::class, 'index'])->name('index');
        Route::get('/{plan}', [AdminPlansController::class, 'show'])->name('show');
    });
