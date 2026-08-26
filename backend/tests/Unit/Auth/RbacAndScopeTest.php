<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Unit\Auth;

use AmarMayor\Auth\ScopeManager;
use AmarMayor\Auth\User;
use AmarMayor\Database\DatabaseManager;
use AmarMayor\Tests\TestCase;
use PDO;

class RbacAndScopeTest extends TestCase
{
    public function testRolePermissionsLookupAndSeparationBetweenSuperAdmins(): void
    {
        $pdo = DatabaseManager::getConnection();

        // 1. Create a Platform Super Admin
        $stmt = $pdo->prepare("
            INSERT INTO users (uuid, phone, user_type, status, preferred_language, created_at)
            VALUES ('test-platform-admin-uuid', '+8801700000001', 'admin', 'active', 'bn', NOW())
        ");
        $stmt->execute();
        $pAdminId = (int)$pdo->lastInsertId();

        $rolePId = (int)$pdo->query("SELECT id FROM roles WHERE slug = 'platform_super_admin'")->fetchColumn();
        $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)")->execute([$pAdminId, $rolePId]);

        // 2. Create a Technical Super Admin
        $stmt = $pdo->prepare("
            INSERT INTO users (uuid, phone, user_type, status, preferred_language, created_at)
            VALUES ('test-tech-admin-uuid', '+8801700000002', 'admin', 'active', 'bn', NOW())
        ");
        $stmt->execute();
        $tAdminId = (int)$pdo->lastInsertId();

        $roleTId = (int)$pdo->query("SELECT id FROM roles WHERE slug = 'technical_super_admin'")->fetchColumn();
        $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)")->execute([$tAdminId, $roleTId]);

        try {
            $pUser = User::findById($pAdminId);
            $this->assertNotNull($pUser);
            $this->assertTrue($pUser->can('governance.assign'), "Platform admin must have governance.assign");
            $this->assertTrue($pUser->can('routing.manage'), "Platform admin must have routing.manage");
            $this->assertFalse($pUser->can('system.backup.manage'), "Platform admin must NOT have technical backup management");

            $tUser = User::findById($tAdminId);
            $this->assertNotNull($tUser);
            $this->assertTrue($tUser->can('system.health.view'), "Tech admin must have system.health.view");
            $this->assertTrue($tUser->can('system.backup.manage'), "Tech admin must have system.backup.manage");
            $this->assertFalse($tUser->can('governance.assign'), "Tech admin must NOT have civic governance assignment");
        } finally {
            $pdo->exec("DELETE FROM user_roles WHERE user_id IN ({$pAdminId}, {$tAdminId})");
            $pdo->exec("DELETE FROM users WHERE id IN ({$pAdminId}, {$tAdminId})");
        }
    }

    public function testWardAndZoneScoping(): void
    {
        $pdo = DatabaseManager::getConnection();
        $scopeManager = new ScopeManager();

        $ward1Id = (int)$pdo->query("SELECT id FROM wards WHERE ward_number = 1")->fetchColumn();
        $ward2Id = (int)$pdo->query("SELECT id FROM wards WHERE ward_number = 2")->fetchColumn();
        $zone1Id = (int)$pdo->query("SELECT id FROM zones WHERE zone_number = 1")->fetchColumn();

        // 1. Create a user scoped strictly to Ward 1
        $stmt = $pdo->prepare("
            INSERT INTO users (uuid, phone, user_type, status, preferred_language, created_at)
            VALUES ('test-scoped-user-uuid', '+8801700000003', 'staff', 'active', 'bn', NOW())
        ");
        $stmt->execute();
        $userId = (int)$pdo->lastInsertId();

        $pdo->prepare("
            INSERT INTO user_scopes (user_id, scope_type, scope_id, effective_from)
            VALUES (?, 'ward', ?, NOW())
        ")->execute([$userId, $ward1Id]);

        try {
            $user = User::findById($userId);
            $this->assertNotNull($user);

            $this->assertTrue($scopeManager->canAccessWard($user, $ward1Id), "User should access Ward 1");
            $this->assertFalse($scopeManager->canAccessWard($user, $ward2Id), "User should NOT access Ward 2");
            $this->assertFalse($scopeManager->canAccessZone($user, $zone1Id), "Ward-scoped user should not have full zone scope");
        } finally {
            $pdo->exec("DELETE FROM user_scopes WHERE user_id = {$userId}");
            $pdo->exec("DELETE FROM users WHERE id = {$userId}");
        }
    }
}
