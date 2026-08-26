<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Unit\Auth;

use AmarMayor\Auth\ScopeManager;
use AmarMayor\Auth\User;
use AmarMayor\Database\DatabaseManager;
use AmarMayor\Support\Security;
use AmarMayor\Tests\TestCase;
use PDO;

class RbacAndScopeTest extends TestCase
{
    public function testRolePermissionsLookupAndSuperAdminBypass(): void
    {
        $pdo = DatabaseManager::getConnection();
        $suffix = bin2hex(random_bytes(4));
        $uuid = Security::uuid();

        $userId = 0;
        try {
            $pdo->prepare("
                INSERT INTO users (uuid, email, user_type, status, preferred_language, created_at)
                VALUES (?, ?, 'admin', 'active', 'bn', NOW())
            ")->execute([$uuid, "admin_{$suffix}@amarmayor.gov.bd"]);
            $userId = (int)$pdo->lastInsertId();

            $superAdminRoleId = (int)$pdo->query("SELECT id FROM roles WHERE slug = 'technical_super_admin'")->fetchColumn();
            $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)")->execute([$userId, $superAdminRoleId]);

            $user = User::findById($userId);
            $this->assertTrue($user->hasRole('technical_super_admin'));
            $this->assertTrue($user->can('complaint.create'));
            $this->assertTrue($user->can('system.security.manage'));
            $this->assertTrue($user->can('any.arbitrary.permission'), "Super Admin must bypass all permissions");
        } finally {
            if ($userId) {
                $pdo->exec("DELETE FROM user_roles WHERE user_id = {$userId}");
                $pdo->exec("DELETE FROM users WHERE id = {$userId}");
            }
        }
    }

    public function testWardAndZoneScoping(): void
    {
        $pdo = DatabaseManager::getConnection();
        $suffix = bin2hex(random_bytes(4));
        $uuid = Security::uuid();

        $userId = 0;
        $scopeId = 0;
        try {
            $pdo->prepare("
                INSERT INTO users (uuid, email, user_type, status, preferred_language, created_at)
                VALUES (?, ?, 'staff', 'active', 'bn', NOW())
            ")->execute([$uuid, "inspector_{$suffix}@amarmayor.gov.bd"]);
            $userId = (int)$pdo->lastInsertId();

            $ward1Id = (int)$pdo->query("SELECT id FROM wards WHERE ward_number = 1")->fetchColumn();
            $ward2Id = (int)$pdo->query("SELECT id FROM wards WHERE ward_number = 2")->fetchColumn();
            $ward1ZoneId = (int)$pdo->query("SELECT zone_id FROM wards WHERE id = {$ward1Id}")->fetchColumn();

            // Assign user scope to Ward 1 only
            $pdo->prepare("
                INSERT INTO user_scopes (user_id, scope_type, scope_id, effective_from)
                VALUES (?, 'ward', ?, NOW())
            ")->execute([$userId, $ward1Id]);
            $scopeId = (int)$pdo->lastInsertId();

            $user = User::findById($userId);

            // User should have access to Ward 1, but NOT Ward 2
            $this->assertTrue(ScopeManager::canAccessWard($user, $ward1Id), "User with Ward 1 scope must access Ward 1");
            $this->assertFalse(ScopeManager::canAccessWard($user, $ward2Id), "User with Ward 1 scope must NOT access Ward 2");

            // Update scope to entire Zone 1
            $pdo->prepare("UPDATE user_scopes SET scope_type = 'zone', scope_id = ? WHERE id = ?")->execute([$ward1ZoneId, $scopeId]);

            // Flush cached scopes
            $user = User::findById($userId);

            $this->assertTrue(ScopeManager::canAccessZone($user, $ward1ZoneId), "User with Zone scope must access Zone");
            $this->assertTrue(ScopeManager::canAccessWard($user, $ward1Id), "User with Zone 1 scope must access Ward 1 (in Zone 1)");
        } finally {
            if ($scopeId) {
                $pdo->exec("DELETE FROM user_scopes WHERE id = {$scopeId}");
            }
            if ($userId) {
                $pdo->exec("DELETE FROM users WHERE id = {$userId}");
            }
        }
    }
}
