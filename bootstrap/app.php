<?php

use App\Http\Middleware\EnsureOwnerHeader;
use App\Http\Middleware\LogHealthCheckRequest;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ThrottleRequestsWithRedis;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'owner.header' => EnsureOwnerHeader::class,
            'log.health-check' => LogHealthCheckRequest::class,
        ]);

        $middleware->prependToPriorityList(ThrottleRequests::class, LogHealthCheckRequest::class);
        $middleware->prependToPriorityList(ThrottleRequestsWithRedis::class, LogHealthCheckRequest::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
