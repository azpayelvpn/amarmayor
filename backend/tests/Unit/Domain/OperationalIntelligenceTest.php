<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Unit\Domain;

use AmarMayor\Database\DatabaseManager;
use AmarMayor\Domain\Intelligence\OperationalIntelligenceService;
use AmarMayor\Tests\TestCase;

class OperationalIntelligenceTest extends TestCase
{
    private OperationalIntelligenceService $intelService;

    public function setUp(): void
    {
        parent::setUp();
        $this->intelService = new OperationalIntelligenceService();
    }

    public function testIntelligenceQueriesAndProjectEvaluation(): void
    {
        $recurring = $this->intelService->detectRecurringComplaints(30, 1);
        $this->assertIsArray($recurring);

        $hotspots = $this->intelService->getHotspots(5);
        $this->assertIsArray($hotspots);

        $eval = $this->intelService->evaluateProjectRequiredIndicators(1, 1);
        $this->assertIsArray($eval);
        $this->assertArrayHasKey('recommend_project_required', $eval);
        $this->assertArrayHasKey('rationale_bn', $eval);
    }
}
