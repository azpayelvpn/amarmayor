<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Unit\Domain;

use AmarMayor\Auth\User;
use AmarMayor\Database\DatabaseManager;
use AmarMayor\Domain\CommandCenter\CommandCenterService;
use AmarMayor\Support\Security;
use AmarMayor\Tests\TestCase;
use PDO;

class CommandCenterAndRolePortalsTest extends TestCase
{
    private CommandCenterService $commandCenterService;

    public function setUp(): void
    {
        parent::setUp();
        $this->commandCenterService = new CommandCenterService();
    }

    public function testExecutiveKpisAndDailyBrief(): void
    {
        $kpis = $this->commandCenterService->getExecutiveKpis();

        $this->assertIsArray($kpis);
        $this->assertArrayHasKey('total_complaints', $kpis);
        $this->assertArrayHasKey('in_progress', $kpis);
        $this->assertArrayHasKey('overdue_count', $kpis);
        $this->assertArrayHasKey('reopen_rate_percent', $kpis);
        $this->assertArrayHasKey('avg_resolution_hours', $kpis);
        $this->assertArrayHasKey('citizen_satisfaction_percent', $kpis);

        $brief = $this->commandCenterService->getDailyBrief();
        $this->assertIsArray($brief);
        $this->assertArrayHasKey('new_complaints_24h', $brief);
        $this->assertArrayHasKey('resolved_24h', $brief);
        $this->assertArrayHasKey('overdue_breaches_24h', $brief);
        $this->assertArrayHasKey('reopened_24h', $brief);
        $this->assertArrayHasKey('directives_issued_24h', $brief);
    }

    public function testRoleDashboardPayloadGeneration(): void
    {
        $pdo = DatabaseManager::getConnection();

        // 1. Create a Mayor user
        $stmt = $pdo->prepare("INSERT INTO users (uuid, phone, user_type, status, preferred_language, created_at) VALUES (?, '+8801722000001', 'admin', 'active', 'bn', NOW())");
        $stmt->execute([Security::uuid()]);
        $mayorUserId = (int)$pdo->lastInsertId();

        $roleMayorId = (int)$pdo->query("SELECT id FROM roles WHERE slug = 'mayor'")->fetchColumn();
        $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)")->execute([$mayorUserId, $roleMayorId]);

        // 2. Create a Supervisor user
        $stmtSup = $pdo->prepare("INSERT INTO users (uuid, phone, user_type, status, preferred_language, created_at) VALUES (?, '+8801722000002', 'staff', 'active', 'bn', NOW())");
        $stmtSup->execute([Security::uuid()]);
        $supUserId = (int)$pdo->lastInsertId();

        $roleSupId = (int)$pdo->query("SELECT id FROM roles WHERE slug = 'supervisor'")->fetchColumn();
        $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)")->execute([$supUserId, $roleSupId]);

        try {
            $mayor = User::findById($mayorUserId);
            $this->assertNotNull($mayor);
            $mayorDash = $this->commandCenterService->getRoleDashboard($mayor);
            $this->assertEquals('executive_command_center', $mayorDash['view_type']);
            $this->assertTrue(isset($mayorDash['kpis']));
            $this->assertTrue(isset($mayorDash['daily_brief']));

            $sup = User::findById($supUserId);
            $this->assertNotNull($sup);
            $supDash = $this->commandCenterService->getRoleDashboard($sup);
            $this->assertEquals('supervisor_workforce', $supDash['view_type']);
            $this->assertTrue(isset($supDash['kpis']));
        } finally {
            $pdo->exec("DELETE FROM user_roles WHERE user_id IN ({$mayorUserId}, {$supUserId})");
            $pdo->exec("DELETE FROM users WHERE id IN ({$mayorUserId}, {$supUserId})");
        }
    }
}
