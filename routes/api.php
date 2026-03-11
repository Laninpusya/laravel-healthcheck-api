<?php

use App\Http\Controllers\Api\V1\HealthCheckController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', HealthCheckController::class)
        // Порядок важен: логируем даже 400/429 ответы и только потом валидируем заголовок.
        ->middleware(['log.health-check', 'throttle:health-check', 'owner.header']);
});
