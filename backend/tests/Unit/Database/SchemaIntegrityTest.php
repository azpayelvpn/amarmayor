<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Unit\Database;

use AmarMayor\Database\DatabaseManager;
use AmarMayor\Tests\TestCase;
use PDO;

class SchemaIntegrityTest extends TestCase
{
    public function testAllRequiredTablesExist(): void
    {
        $pdo = DatabaseManager::getConnection();
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

        $expectedTables = [
            '_migrations',
            'users',
            'roles',
            'permissions',
            'role_permissions',
            'user_roles',
            'user_scopes',
            'user_tokens',
            'cities',
            'zones',
            'wards',
            'ward_zone_history',
            'offices',
            'reserved_seats',
            'reserved_seat_wards',
            'persons',
            'representation_types',
            'representation_assignments',
            'representation_areas',
            'departments',
            'service_units',
            'employees',
            'employee_postings',
            'employee_responsibilities',
            'skills',
            'employee_skills',
            'teams',
            'team_members',
            'complaint_categories',
            'complaint_subcategories',
            'operational_classifications',
            'priorities',
            'service_deadline_rules',
            'routing_rules',
            'complaints',
            'complaint_locations',
            'complaint_media',
            'complaint_status_history',
            'complaint_ownership_history',
            'complaint_supporters',
            'field_tasks',
            'task_evidence',
            'support_requests',
            'citizen_feedback',
            'executive_attention',
            'executive_directives',
            'explanation_requests',
            'complaint_messages',
            'internal_notes',
            'office_messages',
            'city_notices',
            'citizen_pulse',
            'notifications',
            'notification_preferences',
            'audit_logs',
            'background_jobs',
            'settings',
        ];

        foreach ($expectedTables as $table) {
            $this->assertTrue(in_array($table, $tables, true), "Database must contain table: {$table}");
        }
    }

    public function testCheckConstraintsPreventInvalidData(): void
    {
        $pdo = DatabaseManager::getConnection();

        // 1. Latitude range check constraint
        $failedLat = false;
        try {
            $pdo->exec("INSERT INTO complaints (public_complaint_number, citizen_user_id, category_id, subcategory_id, ward_id, zone_id, description, submitted_at) 
                        VALUES ('CHK-LAT-TEST', 1, 1, 1, 1, 1, 'Test', NOW())");
            $cId = $pdo->lastInsertId();
            $pdo->exec("INSERT INTO complaint_locations (complaint_id, latitude, longitude) VALUES ({$cId}, 150.00000000, 90.00000000)");
        } catch (\Throwable $e) {
            $failedLat = true;
        } finally {
            $pdo->exec("DELETE FROM complaints WHERE public_complaint_number = 'CHK-LAT-TEST'");
        }
        $this->assertTrue($failedLat, "Check constraint must reject latitude > 90 degrees");

        // 2. Effective date check constraint
        $failedDates = false;
        try {
            $pdo->exec("INSERT INTO user_scopes (user_id, scope_type, effective_from, effective_to) 
                        VALUES (1, 'citywide', '2026-08-26 12:00:00', '2026-08-25 12:00:00')");
        } catch (\Throwable $e) {
            $failedDates = true;
        }
        $this->assertTrue($failedDates, "Check constraint must reject effective_to < effective_from");
    }
}
