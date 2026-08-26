<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Unit\Domain;

use AmarMayor\Database\DatabaseManager;
use AmarMayor\Domain\ComplaintConfig\DeadlineConfigService;
use AmarMayor\Tests\TestCase;
use DateTimeImmutable;
use PDO;

class DeadlineConfigServiceTest extends TestCase
{
    private DeadlineConfigService $deadlineService;

    public function setUp(): void
    {
        parent::setUp();
        $this->deadlineService = new DeadlineConfigService();
    }

    public function testDefaultSlaHoursFallback(): void
    {
        $pdo = DatabaseManager::getConnection();
        $catId = (int)$pdo->query("SELECT id FROM complaint_categories LIMIT 1")->fetchColumn();

        // System defaults
        $h1 = $this->deadlineService->getExpectedHours($catId, null, 'p1_critical', 'quick_action');
        $this->assertEquals(8, $h1);

        $h2 = $this->deadlineService->getExpectedHours($catId, null, 'p2_high', 'quick_action');
        $this->assertEquals(24, $h2);

        $h3 = $this->deadlineService->getExpectedHours($catId, null, 'p3_normal', 'quick_action');
        $this->assertEquals(48, $h3);

        $h4 = $this->deadlineService->getExpectedHours($catId, null, 'p4_low', 'quick_action');
        $this->assertEquals(120, $h4);
    }

    public function testSpecificDeadlineRuleOverride(): void
    {
        $pdo = DatabaseManager::getConnection();
        $catId = (int)$pdo->query("SELECT id FROM complaint_categories WHERE slug = 'cleanliness'")->fetchColumn();
        $subId = (int)$pdo->query("SELECT id FROM complaint_subcategories WHERE slug = 'dead_animal'")->fetchColumn();

        $ruleId = 0;
        try {
            // Override P1 Dead Animal to 4 hours (emergency quick action)
            $ruleId = $this->deadlineService->createDeadlineRule([
                'category_id' => $catId,
                'subcategory_id' => $subId,
                'priority' => 'p1_critical',
                'operational_classification' => 'quick_action',
                'expected_hours' => 4,
            ]);

            $hours = $this->deadlineService->getExpectedHours($catId, $subId, 'p1_critical', 'quick_action');
            $this->assertEquals(4, $hours);

            $submission = new DateTimeImmutable('2026-08-26 10:00:00');
            $deadline = $this->deadlineService->calculateDeadline($catId, $subId, 'p1_critical', 'quick_action', $submission);

            $this->assertEquals('2026-08-26 14:00:00', $deadline->format('Y-m-d H:i:s'));
        } finally {
            if ($ruleId) {
                $pdo->exec("DELETE FROM service_deadline_rules WHERE id = {$ruleId}");
            }
        }
    }
}
