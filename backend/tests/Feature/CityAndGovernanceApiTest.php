<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Feature;

use AmarMayor\Database\DatabaseManager;
use AmarMayor\Tests\TestCase;
use PDO;

class CityAndGovernanceApiTest extends TestCase
{
    public function testGetCityProfileEndpoint(): void
    {
        $response = $this->get('/api/v1/city/profile');
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals(3, $data['data']['total_zones']);
        $this->assertEquals(33, $data['data']['total_wards']);
    }

    public function testGetCityZonesEndpoint(): void
    {
        $response = $this->get('/api/v1/city/zones');
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertCount(3, $data['data']);
    }

    public function testGetWardDetailsEndpoint(): void
    {
        $pdo = DatabaseManager::getConnection();
        $ward1Id = (int)$pdo->query("SELECT id FROM wards WHERE ward_number = 1")->fetchColumn();

        $response = $this->get("/api/v1/city/wards/{$ward1Id}");
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals(1, (int)$data['data']['ward_number']);
        $this->assertEquals('1', (string)$data['data']['zone_number']);
    }

    public function testGetWorkforceDepartmentsEndpoint(): void
    {
        $response = $this->get('/api/v1/workforce/departments');
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assert(count($data['data']) >= 6);
    }
}
