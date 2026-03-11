<?php

namespace App\Services;

use App\Models\HealthCheckRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

class HealthCheckRequestLogger
{
    public function store(Request $request, int $statusCode, ?array $responsePayload = null): void
    {
        try {
            // Логирование оставляем best-effort, чтобы health-check сам вернул состояние инфраструктуры.
            HealthCheckRequest::query()->create([
                'owner_uuid' => $this->normalizeOwnerHeader($request->header('X-Owner')),
                'method' => $request->method(),
                'path' => ltrim($request->path(), '/'),
                'ip_address' => $request->ip(),
                'status_code' => $statusCode,
                'response_payload' => $responsePayload,
            ]);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function normalizeOwnerHeader(mixed $value): ?string
    {
        return is_string($value) && Str::isUuid($value) ? $value : null;
    }
}
