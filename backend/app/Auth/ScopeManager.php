<?php

declare(strict_types=1);

namespace AmarMayor\Auth;

use AmarMayor\Database\DatabaseManager;
use PDO;

class ScopeManager
{
    /**
     * Checks if user has permission to access a specific Ward.
     */
    public static function canAccessWard(User $user, int $wardId): bool
    {
        // 1. Super admins, executive leadership, or citywide scope have full access
        if ($user->hasRole(['platform_super_admin', 'technical_super_admin', 'mayor', 'administrator', 'ceo'])) {
            return true;
        }

        $scopes = $user->getScopes();
        foreach ($scopes as $scope) {
            if ($scope->scopeType === 'citywide') {
                return true;
            }
            if ($scope->scopeType === 'ward' && $scope->scopeId === $wardId) {
                return true;
            }
            if ($scope->scopeType === 'zone' && $scope->scopeId !== null) {
                // Check if ward belongs to this zone
                if (self::isWardInZone($wardId, $scope->scopeId)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Checks if user has permission to access a specific Zone.
     */
    public static function canAccessZone(User $user, int $zoneId): bool
    {
        if ($user->hasRole(['platform_super_admin', 'technical_super_admin', 'mayor', 'administrator', 'ceo'])) {
            return true;
        }

        $scopes = $user->getScopes();
        foreach ($scopes as $scope) {
            if ($scope->scopeType === 'citywide') {
                return true;
            }
            if ($scope->scopeType === 'zone' && $scope->scopeId === $zoneId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if user has permission to access a specific Department.
     */
    public static function canAccessDepartment(User $user, int $departmentId): bool
    {
        if ($user->hasRole(['platform_super_admin', 'technical_super_admin', 'mayor', 'administrator', 'ceo'])) {
            return true;
        }

        $scopes = $user->getScopes();
        foreach ($scopes as $scope) {
            if ($scope->scopeType === 'citywide') {
                return true;
            }
            if ($scope->scopeType === 'department' && $scope->scopeId === $departmentId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if a ward is within a given zone.
     */
    public static function isWardInZone(int $wardId, int $zoneId): bool
    {
        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM wards WHERE id = ? AND zone_id = ?");
        $stmt->execute([$wardId, $zoneId]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
