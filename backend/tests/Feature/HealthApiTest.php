<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Feature;

use AmarMayor\Tests\TestCase;

class HealthApiTest extends TestCase
{
    public function testHealthApiEndpoint(): void
    {
        $this->setUp();
        $res = $this->get('/api/v1/health');

        $this->assertEquals(200, $res->getStatusCode());
        $payload = json_decode($res->getContent(), true);

        $this->assertTrue($payload['success']);
        $this->assertTrue(isset($payload['data']['status']));
        $this->assertTrue(isset($payload['data']['services']['database']));
        $this->assertTrue(isset($payload['data']['services']['cache_fast_storage']));
        $this->assertTrue(isset($payload['meta']['request_id']));
    }
}
