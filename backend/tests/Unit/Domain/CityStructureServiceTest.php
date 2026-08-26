<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Unit\Domain;

use AmarMayor\Database\DatabaseManager;
use AmarMayor\Domain\City\CityStructureService;
use AmarMayor\Tests\TestCase;
use PDO;

class CityStructureServiceTest extends TestCase
{
    private CityStructureService $cityService;

    public function setUp(): void
    {
        parent::setUp();
        $this->cityService = new CityStructureService();
    }

    public function testGetCityProfile(): void
    {
        $profile = $this->cityService->getCityProfile();
        $this->assertNotNull($profile['id'] ?? null);
        $this->assertEquals(3, $profile['total_zones']);
        $this->assertEquals(33, $profile['total_wards']);
        $this->assertEquals(11, $profile['total_reserved_seats']);
    }

    public function testGetZonesWithNestedWards(): void
    {
        $zones = $this->cityService->getZones(true);
        $this->assertCount(3, $zones);

        // Zone 1 = 10 wards (1-10)
        $this->assertEquals(1, $zones[0]['zone_number']);
        $this->assertCount(10, $zones[0]['wards']);

        // Zone 2 = 12 wards (11-22)
        $this->assertEquals(2, $zones[1]['zone_number']);
        $this->assertCount(12, $zones[1]['wards']);

        // Zone 3 = 11 wards (23-33)
        $this->assertEquals(3, $zones[2]['zone_number']);
        $this->assertCount(11, $zones[2]['wards']);
    }

    public function testUpdateWardZoneAndHistory(): void
    {
        $pdo = DatabaseManager::getConnection();
        $ward1Id = (int)$pdo->query("SELECT id FROM wards WHERE ward_number = 1")->fetchColumn();
        $zone1Id = (int)$pdo->query("SELECT id FROM zones WHERE zone_number = 1")->fetchColumn();
        $zone2Id = (int)$pdo->query("SELECT id FROM zones WHERE zone_number = 2")->fetchColumn();

        $transferDate = date('Y-m-d H:i:s');
        $restoreDate = date('Y-m-d H:i:s', strtotime('+1 day'));

        // 1. Transfer Ward 1 from Zone 1 to Zone 2
        $this->cityService->updateWardZone($ward1Id, $zone2Id, 'GOV-TRANSFER-001', $transferDate);

        $currentZoneId = (int)$pdo->query("SELECT zone_id FROM wards WHERE id = {$ward1Id}")->fetchColumn();
        $this->assertEquals($zone2Id, $currentZoneId);

        // Verify History Entry
        $history = $pdo->query("
            SELECT * FROM ward_zone_history 
            WHERE ward_id = {$ward1Id} 
            ORDER BY id DESC LIMIT 1
        ")->fetch(PDO::FETCH_ASSOC);

        $this->assertNotNull($history);
        $this->assertEquals($zone2Id, (int)$history['zone_id']);
        $this->assertEquals('GOV-TRANSFER-001', $history['authority_order']);

        // 2. Restore back to Zone 1
        $this->cityService->updateWardZone($ward1Id, $zone1Id, 'RESTORE-001', $restoreDate);
        $restoredZoneId = (int)$pdo->query("SELECT zone_id FROM wards WHERE id = {$ward1Id}")->fetchColumn();
        $this->assertEquals($zone1Id, $restoredZoneId);

        // Clean up history records for this test
        $pdo->exec("DELETE FROM ward_zone_history WHERE ward_id = {$ward1Id}");
    }

    public function testAssignReservedSeatWards(): void
    {
        $pdo = DatabaseManager::getConnection();
        $seat1Id = (int)$pdo->query("SELECT id FROM reserved_seats WHERE seat_number = 1")->fetchColumn();
        $ward1Id = (int)$pdo->query("SELECT id FROM wards WHERE ward_number = 1")->fetchColumn();
        $ward2Id = (int)$pdo->query("SELECT id FROM wards WHERE ward_number = 2")->fetchColumn();

        // Assign Ward 1 and 2 to Reserved Seat 1 via data configuration
        $this->cityService->assignReservedSeatWards($seat1Id, [$ward1Id, $ward2Id]);

        $seats = $this->cityService->getReservedSeats();
        $seat1 = null;
        foreach ($seats as $s) {
            if ($s['id'] === $seat1Id) {
                $seat1 = $s;
                break;
            }
        }

        $this->assertNotNull($seat1);
        $this->assertCount(2, $seat1['covered_wards']);

        // Clean up
        $pdo->exec("DELETE FROM reserved_seat_wards WHERE reserved_seat_id = {$seat1Id}");
    }
}
