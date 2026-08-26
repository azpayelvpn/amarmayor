<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Feature;

use AmarMayor\Support\Config;
use AmarMayor\Tests\TestCase;

class HealthApiHardeningTest extends TestCase
{
    public function testProductionHealthApiDoesNotLeakInfrastructureDetails(): void
    {
        $this->setUp();

        // Simulate Production Environment
        Config::set('app.env', 'production');
        Config::set('app.debug', false);

        $res = $this->get('/api/v1/health');

        $this->assertEquals(200, $res->getStatusCode());
        $payload = json_decode($res->getContent(), true);

        $this->assertTrue($payload['success']);
        $this->assertEquals('healthy', $payload['data']['status']);

        // Assert NO internal infrastructure leaks exist in production
        $this->assertFalse(isset($payload['data']['services']), "Production health API must not expose internal services map");
        $this->assertFalse(isset($payload['data']['app']['env']), "Production health API must not expose environment variable values");
        $this->assertFalse(str_contains($res->getContent(), 'memory_fallback'), "Production health API must not leak memory_fallback");
        $this->assertFalse(str_contains($res->getContent(), 'phpredis'), "Production health API must not leak driver names");
        $this->assertFalse(str_contains($res->getContent(), 'mysql'), "Production health API must not leak database engine");

        // Reset to local debug for subsequent tests
        Config::set('app.env', 'local');
        Config::set('app.debug', true);
    }
}
