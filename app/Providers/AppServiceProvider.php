<?php

namespace App\Providers;

use App\Services\HealthCheckRequestLogger;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('health-check', function (Request $request): Limit {
            // Ограничиваем запросы по владельцу, а если заголовка нет — по IP.
            return Limit::perMinute(60)
                ->by($request->header('X-Owner', $request->ip()))
                ->response(function (Request $request, array $headers) {
                    $payload = ['message' => 'Too Many Attempts.'];

                    app(HealthCheckRequestLogger::class)->store($request, 429, $payload);

                    return response()->json($payload, 429, $headers);
                });
        });
    }
}
