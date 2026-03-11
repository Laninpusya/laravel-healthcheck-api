<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\InfrastructureHealthService;
use Illuminate\Http\JsonResponse;

class HealthCheckController extends Controller
{
    public function __invoke(InfrastructureHealthService $healthService): JsonResponse
    {
        $services = $healthService->check();
        $statusCode = in_array(false, $services, true) ? 500 : 200;

        return response()->json($services, $statusCode);
    }
}
