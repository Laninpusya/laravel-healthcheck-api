<?php

namespace Tests\Feature;

use App\Services\InfrastructureHealthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_ok_when_all_services_are_available(): void
    {
        $ownerUuid = (string) Str::uuid();

        $this->mock(InfrastructureHealthService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('check')
                ->once()
                ->andReturn([
                    'db' => true,
                    'cache' => true,
                ]);
        });

        $response = $this->withHeader('X-Owner', $ownerUuid)
            ->getJson('/api/v1/health');

        $response
            ->assertOk()
            ->assertExactJson([
                'db' => true,
                'cache' => true,
            ]);

        $this->assertDatabaseHas('health_check_requests', [
            'owner_uuid' => $ownerUuid,
            'method' => 'GET',
            'path' => 'api/v1/health',
            'status_code' => 200,
        ]);
    }

    public function test_it_returns_internal_server_error_when_any_service_is_down(): void
    {
        $ownerUuid = (string) Str::uuid();

        $this->mock(InfrastructureHealthService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('check')
                ->once()
                ->andReturn([
                    'db' => true,
                    'cache' => false,
                ]);
        });

        $response = $this->withHeader('X-Owner', $ownerUuid)
            ->getJson('/api/v1/health');

        $response
            ->assertStatus(500)
            ->assertExactJson([
                'db' => true,
                'cache' => false,
            ]);

        $this->assertDatabaseHas('health_check_requests', [
            'owner_uuid' => $ownerUuid,
            'status_code' => 500,
        ]);
    }

    public function test_it_rejects_requests_without_a_valid_owner_header(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response
            ->assertBadRequest()
            ->assertExactJson([
                'message' => 'The X-Owner header must contain a valid UUID.',
            ]);

        $this->assertDatabaseHas('health_check_requests', [
            'owner_uuid' => null,
            'status_code' => 400,
        ]);
    }

    public function test_it_throttles_after_sixty_requests_per_minute(): void
    {
        $ownerUuid = (string) Str::uuid();

        $this->mock(InfrastructureHealthService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('check')
                ->times(60)
                ->andReturn([
                    'db' => true,
                    'cache' => true,
                ]);
        });

        for ($attempt = 0; $attempt < 60; $attempt++) {
            $this->withHeader('X-Owner', $ownerUuid)
                ->getJson('/api/v1/health')
                ->assertOk();
        }

        $this->withHeader('X-Owner', $ownerUuid)
            ->getJson('/api/v1/health')
            ->assertStatus(429);

        $this->assertDatabaseCount('health_check_requests', 61);
        $this->assertDatabaseHas('health_check_requests', [
            'owner_uuid' => $ownerUuid,
            'status_code' => 429,
        ]);
    }
}
