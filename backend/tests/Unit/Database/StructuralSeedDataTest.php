<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Unit\Database;

use AmarMayor\Database\DatabaseManager;
use AmarMayor\Tests\TestCase;
use PDO;

class StructuralSeedDataTest extends TestCase
{
    public function testInitialCityStructureCounts(): void
    {
        $pdo = DatabaseManager::getConnection();

        // City
        $cityCount = (int)$pdo->query("SELECT COUNT(*) FROM cities WHERE slug = 'mcc'")->fetchColumn();
        $this->assertEquals(1, $cityCount);

        // Zones = 3
        $zoneCount = (int)$pdo->query("SELECT COUNT(*) FROM zones")->fetchColumn();
        $this->assertEquals(3, $zoneCount);

        // Wards = 33
        $wardCount = (int)$pdo->query("SELECT COUNT(*) FROM wards")->fetchColumn();
        $this->assertEquals(33, $wardCount);

        // Reserved Seats = 11
        $seatCount = (int)$pdo->query("SELECT COUNT(*) FROM reserved_seats")->fetchColumn();
        $this->assertEquals(11, $seatCount);

        // Reserved Seat Wards coverage MUST be 0 (No fabricated grouping)
        $mappedSeatCount = (int)$pdo->query("SELECT COUNT(*) FROM reserved_seat_wards")->fetchColumn();
        $this->assertEquals(0, $mappedSeatCount, "Reserved seat ward grouping must not be fabricated");
    }

    public function testExactApprovedZoneWardMapping(): void
    {
        $pdo = DatabaseManager::getConnection();

        $expectedMapping = [
            1 => [1, 2, 4, 6, 11, 12, 27, 28, 29, 30], // 10 Wards
            2 => [3, 5, 7, 8, 9, 10, 16, 17, 18, 31, 32, 33], // 12 Wards
            3 => [13, 14, 15, 19, 20, 21, 22, 23, 24, 25, 26], // 11 Wards
        ];

        foreach ($expectedMapping as $zoneNum => $expectedWards) {
            $zoneId = (int)$pdo->query("SELECT id FROM zones WHERE zone_number = {$zoneNum}")->fetchColumn();
            $stmt = $pdo->prepare("SELECT ward_number FROM wards WHERE zone_id = ? ORDER BY ward_number ASC");
            $stmt->execute([$zoneId]);
            $actualWards = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $this->assertEquals(count($expectedWards), count($actualWards), "Zone {$zoneNum} must contain exact ward count");
            $this->assertEquals($expectedWards, array_map('intval', $actualWards), "Zone {$zoneNum} must contain exact approved ward numbers");
        }
    }

    public function testComplaintTaxonomiesAndRolesSeeded(): void
    {
        $pdo = DatabaseManager::getConnection();

        // 12 Complaint Categories
        $catCount = (int)$pdo->query("SELECT COUNT(*) FROM complaint_categories")->fetchColumn();
        $this->assertEquals(12, $catCount);

        // Subcategories >= 20
        $subcatCount = (int)$pdo->query("SELECT COUNT(*) FROM complaint_subcategories")->fetchColumn();
        $this->assertTrue($subcatCount >= 20);

        // Operational Classifications = 6
        $classCount = (int)$pdo->query("SELECT COUNT(*) FROM operational_classifications")->fetchColumn();
        $this->assertEquals(6, $classCount);

        // Priorities = 4
        $priorityCount = (int)$pdo->query("SELECT COUNT(*) FROM priorities")->fetchColumn();
        $this->assertEquals(4, $priorityCount);

        // Roles = 22
        $roleCount = (int)$pdo->query("SELECT COUNT(*) FROM roles")->fetchColumn();
        $this->assertEquals(22, $roleCount, "Must seed exact 22 canonical roles");

        // Permissions = 68
        $permissionCount = (int)$pdo->query("SELECT COUNT(*) FROM permissions")->fetchColumn();
        $this->assertEquals(68, $permissionCount, "Must seed all 68 granular permissions");

        // Representation Types = 8
        $repCount = (int)$pdo->query("SELECT COUNT(*) FROM representation_types")->fetchColumn();
        $this->assertEquals(8, $repCount, "Must seed 8 representation types");

        // Skills = 9
        $skillCount = (int)$pdo->query("SELECT COUNT(*) FROM skills")->fetchColumn();
        $this->assertEquals(9, $skillCount, "Must seed 9 municipal skills");
    }
}
