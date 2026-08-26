<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Feature;

use AmarMayor\Database\DatabaseManager;
use AmarMayor\Tests\TestCase;
use PDO;

class ComplaintConfigApiTest extends TestCase
{
    public function testGetComplaintCategoriesEndpoint(): void
    {
        $response = $this->get('/api/v1/complaints/categories');
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assert(count($data['data']) >= 10);
    }

    public function testGetSubcategoryDetailsEndpoint(): void
    {
        $pdo = DatabaseManager::getConnection();
        $subId = (int)$pdo->query("SELECT id FROM complaint_subcategories WHERE slug = 'dustbin_overflow'")->fetchColumn();

        $response = $this->get("/api/v1/complaints/subcategories/{$subId}");
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals('dustbin_overflow', $data['data']['slug']);
    }

    public function testGetRoutingGapsEndpoint(): void
    {
        $response = $this->get('/api/v1/complaints/config/routing-gaps');
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('total_gaps', $data['data']);
        $this->assertArrayHasKey('gaps', $data['data']);
    }

    public function testGetDeadlinesEndpoint(): void
    {
        $response = $this->get('/api/v1/complaints/config/deadlines');
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertIsArray($data['data']);
    }
}
