<?php

namespace App\Http\Middleware;

use App\Services\HealthCheckRequestLogger;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class LogHealthCheckRequest
{
    public function __construct(
        private readonly HealthCheckRequestLogger $requestLogger,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        $responsePayload = null;

        try {
            $response = $next($request);
            $statusCode = $response->getStatusCode();

            if ($response instanceof JsonResponse) {
                $responsePayload = $response->getData(true);
            }

            return $response;
        } catch (Throwable $exception) {
            if ($exception instanceof HttpExceptionInterface) {
                $statusCode = $exception->getStatusCode();
            }

            throw $exception;
        } finally {
            $this->requestLogger->store($request, $statusCode, $responsePayload);
        }
    }
}
