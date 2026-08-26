<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Unit\Domain;

use AmarMayor\Database\DatabaseManager;
use AmarMayor\Domain\ComplaintConfig\RoutingConfigService;
use AmarMayor\Tests\TestCase;
use PDO;

class RoutingConfigServiceTest extends TestCase
{
    private RoutingConfigService $routingService;

    public function setUp(): void
    {
        parent::setUp();
        $this->routingService = new RoutingConfigService();
    }

    public function testDeterministicFourTierRoutingCascade(): void
    {
        $pdo = DatabaseManager::getConnection();
        $catId = (int)$pdo->query("SELECT id FROM complaint_categories WHERE slug = 'cleanliness'")->fetchColumn();
        $subId = (int)$pdo->query("SELECT id FROM complaint_subcategories WHERE slug = 'dustbin_overflow'")->fetchColumn();
        $ward1Id = (int)$pdo->query("SELECT id FROM wards WHERE ward_number = 1")->fetchColumn();
        $ward2Id = (int)$pdo->query("SELECT id FROM wards WHERE ward_number = 2")->fetchColumn();
        $deptId = (int)$pdo->query("SELECT id FROM departments WHERE slug = 'waste_management'")->fetchColumn();

        $ruleIds = [];
        try {
            // Tier 4: Category Default Rule
            $r4 = $this->routingService->createRoutingRule([
                'category_id' => $catId,
                'department_id' => $deptId,
                'effective_from' => '2026-01-01 00:00:00',
            ]);
            $ruleIds[] = $r4;

            // When querying Ward 2 with no specific rule, it falls back to Tier 4
            $resolved4 = $this->routingService->resolveRoutingRule($catId, null, $ward2Id);
            $this->assertNotNull($resolved4);
            $this->assertEquals($r4, (int)$resolved4['id']);

            // Tier 2: Category + Ward 1 Rule
            $r2 = $this->routingService->createRoutingRule([
                'category_id' => $catId,
                'ward_id' => $ward1Id,
                'department_id' => $deptId,
                'effective_from' => '2026-01-01 00:00:00',
            ]);
            $ruleIds[] = $r2;

            // Querying Category for Ward 1 now resolves Tier 2
            $resolved2 = $this->routingService->resolveRoutingRule($catId, null, $ward1Id);
            $this->assertNotNull($resolved2);
            $this->assertEquals($r2, (int)$resolved2['id']);

            // Tier 1: Specific Subcategory + Ward 1 Rule
            $r1 = $this->routingService->createRoutingRule([
                'category_id' => $catId,
                'subcategory_id' => $subId,
                'ward_id' => $ward1Id,
                'department_id' => $deptId,
                'effective_from' => '2026-01-01 00:00:00',
            ]);
            $ruleIds[] = $r1;

            // Querying Subcategory for Ward 1 resolves most specific Tier 1
            $resolved1 = $this->routingService->resolveRoutingRule($catId, $subId, $ward1Id);
            $this->assertNotNull($resolved1);
            $this->assertEquals($r1, (int)$resolved1['id']);
        } finally {
            if (!empty($ruleIds)) {
                $in = implode(',', $ruleIds);
                $pdo->exec("DELETE FROM routing_rules WHERE id IN ({$in})");
            }
        }
    }

    public function testRoutingGapDetection(): void
    {
        $gaps = $this->routingService->detectRoutingGaps();
        // Since test database doesn't have rules for all categories across all 33 wards yet, gaps should be detected
        $this->assertIsArray($gaps);
    }
}
