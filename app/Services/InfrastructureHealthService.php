<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class InfrastructureHealthService
{
    public function check(): array
    {
        return [
            'db' => $this->databaseIsAvailable(),
            'cache' => $this->cacheIsAvailable(),
        ];
    }

    private function databaseIsAvailable(): bool
    {
        try {
            DB::select('SELECT 1');

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function cacheIsAvailable(): bool
    {
        $cacheKey = 'health-check:'.Str::uuid()->toString();

        try {
            // Проверяем Redis через стандартный cache-store, а не прямой ping.
            Cache::store('redis')->put($cacheKey, 'ok', now()->addSeconds(10));

            $isAvailable = Cache::store('redis')->get($cacheKey) === 'ok';

            Cache::store('redis')->forget($cacheKey);

            return $isAvailable;
        } catch (Throwable) {
            return false;
        }
    }
}
