<?php

declare(strict_types=1);

namespace AmarMayor\Domain\City;

use AmarMayor\Database\DatabaseManager;
use InvalidArgumentException;
use PDO;

class CityStructureService
{
    /**
     * Get primary City Corporation profile with summary statistics.
     */
    public function getCityProfile(): array
    {
        $pdo = DatabaseManager::getConnection();
        $city = $pdo->query("SELECT * FROM cities LIMIT 1")->fetch(PDO::FETCH_ASSOC);

        if (!$city) {
            return [];
        }

        $zoneCount = (int)$pdo->query("SELECT COUNT(*) FROM zones WHERE status = 'active'")->fetchColumn();
        $wardCount = (int)$pdo->query("SELECT COUNT(*) FROM wards WHERE status = 'active'")->fetchColumn();
        $reservedSeatCount = (int)$pdo->query("SELECT COUNT(*) FROM reserved_seats WHERE status = 'active'")->fetchColumn();

        return [
            'id' => (int)$city['id'],
            'name_bn' => $city['name_bn'],
            'name_en' => $city['name_en'],
            'total_zones' => $zoneCount,
            'total_wards' => $wardCount,
            'total_reserved_seats' => $reservedSeatCount,
        ];
    }

    /**
     * Get all active zones, optionally with nested wards.
     */
    public function getZones(bool $includeWards = true): array
    {
        $pdo = DatabaseManager::getConnection();
        $zones = $pdo->query("
            SELECT id, zone_number, name_bn, name_en, office_address, status 
            FROM zones 
            WHERE status = 'active' 
            ORDER BY zone_number ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        if (!$includeWards) {
            return $zones;
        }

        foreach ($zones as &$zone) {
            $stmt = $pdo->prepare("
                SELECT id, ward_number, name_bn, name_en, status 
                FROM wards 
                WHERE zone_id = ? AND status = 'active' 
                ORDER BY ward_number ASC
            ");
            $stmt->execute([$zone['id']]);
            $zone['wards'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $zones;
    }

    /**
     * Get details for a specific ward, including its zone and active representatives.
     */
    public function getWardDetails(int $wardId): ?array
    {
        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("
            SELECT w.id, w.ward_number, w.name_bn, w.name_en, w.zone_id, w.status,
                   z.zone_number, z.name_bn as zone_name_bn, z.name_en as zone_name_en
            FROM wards w
            LEFT JOIN zones z ON z.id = w.zone_id
            WHERE w.id = ?
            LIMIT 1
        ");
        $stmt->execute([$wardId]);
        $ward = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$ward) {
            return null;
        }

        // Active general representative (Elected Councillor OR Responsible Officer)
        $repStmt = $pdo->prepare("
            SELECT p.id as person_id, p.full_name_bn, p.full_name_en, p.official_phone as phone, p.photo_url,
                   rt.slug as role_slug, rt.name_bn as role_name_bn, rt.name_en as role_name_en,
                   ra.authority_basis, ra.raw_source_title, ra.status_note, ra.effective_from, ra.effective_to, ra.official_order_no as order_number
            FROM representation_areas area
            INNER JOIN representation_assignments ra ON ra.id = area.representation_assignment_id
            INNER JOIN persons p ON p.id = ra.person_id
            INNER JOIN representation_types rt ON rt.id = ra.representation_type_id
            WHERE area.area_type = 'ward' AND area.area_id = ?
              AND (ra.effective_to IS NULL OR ra.effective_to >= NOW())
              AND ra.effective_from <= NOW()
        ");
        $repStmt->execute([$wardId]);
        $ward['active_representatives'] = $repStmt->fetchAll(PDO::FETCH_ASSOC);

        // Active operational officer & leave substitute
        $opStmt = $pdo->prepare("
            SELECT 
                p_pri.full_name_bn as op_name_bn, p_pri.full_name_en as op_name_en, p_pri.official_phone as op_phone,
                e_pri.designation_bn as op_designation_bn, e_pri.designation_en as op_designation_en, e_pri.employee_code as op_code,
                p_sub.full_name_bn as sub_name_bn, p_sub.full_name_en as sub_name_en, p_sub.official_phone as sub_phone,
                e_sub.designation_bn as sub_designation_bn, e_sub.designation_en as sub_designation_en
            FROM employee_responsibilities er
            INNER JOIN employees e_pri ON e_pri.id = er.employee_id
            INNER JOIN persons p_pri ON p_pri.id = e_pri.person_id
            LEFT JOIN employees e_sub ON e_sub.id = er.substitute_employee_id
            LEFT JOIN persons p_sub ON p_sub.id = e_sub.person_id
            WHERE er.area_type = 'ward' AND er.area_id = ? AND er.is_demo = 0 AND er.responsibility_type = 'primary'
              AND er.effective_from <= NOW() AND (er.effective_to IS NULL OR er.effective_to >= NOW())
            LIMIT 1
        ");
        $opStmt->execute([$wardId]);
        $opRow = $opStmt->fetch(PDO::FETCH_ASSOC);
        $ward['operational_officer'] = $opRow ?: null;

        return $ward;
    }

    /**
     * Transfer a ward to a new zone with historical auditing.
     */
    public function updateWardZone(int $wardId, int $newZoneId, ?string $orderNumber = null, ?string $effectiveDate = null): void
    {
        $pdo = DatabaseManager::getConnection();

        $currentWard = $pdo->query("SELECT zone_id FROM wards WHERE id = {$wardId}")->fetch(PDO::FETCH_ASSOC);
        if (!$currentWard) {
            throw new InvalidArgumentException("Ward ID {$wardId} does not exist.");
        }

        $oldZoneId = (int)$currentWard['zone_id'];
        if ($oldZoneId === $newZoneId) {
            return;
        }

        $effDate = $effectiveDate ?: date('Y-m-d H:i:s');

        $pdo->beginTransaction();
        try {
            // Close old zone history if present
            $pdo->prepare("UPDATE ward_zone_history SET effective_to = GREATEST(effective_from, ?) WHERE ward_id = ? AND effective_to IS NULL")
                ->execute([$effDate, $wardId]);

            // Update ward
            $pdo->prepare("UPDATE wards SET zone_id = ? WHERE id = ?")->execute([$newZoneId, $wardId]);

            // Insert into history
            $stmt = $pdo->prepare("
                INSERT INTO ward_zone_history (ward_id, zone_id, authority_order, effective_from, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$wardId, $newZoneId, $orderNumber, $effDate]);

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Get all reserved seats and their mapped wards.
     */
    public function getReservedSeats(): array
    {
        $pdo = DatabaseManager::getConnection();
        $seats = $pdo->query("
            SELECT id, seat_number, name_bn, name_en, status 
            FROM reserved_seats 
            WHERE status = 'active' 
            ORDER BY seat_number ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        foreach ($seats as &$seat) {
            $stmt = $pdo->prepare("
                SELECT w.id, w.ward_number, w.name_bn, w.name_en 
                FROM reserved_seat_wards rsw
                INNER JOIN wards w ON w.id = rsw.ward_id
                WHERE rsw.reserved_seat_id = ?
                  AND (rsw.effective_to IS NULL OR rsw.effective_to >= NOW())
                  AND rsw.effective_from <= NOW()
                ORDER BY w.ward_number ASC
            ");
            $stmt->execute([$seat['id']]);
            $seat['covered_wards'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $seats;
    }

    /**
     * Configures/assigns wards to a reserved women councillor seat.
     */
    public function assignReservedSeatWards(int $reservedSeatId, array $wardIds): void
    {
        $pdo = DatabaseManager::getConnection();
        $pdo->beginTransaction();
        try {
            $pdo->prepare("DELETE FROM reserved_seat_wards WHERE reserved_seat_id = ?")->execute([$reservedSeatId]);

            $stmt = $pdo->prepare("
                INSERT INTO reserved_seat_wards (reserved_seat_id, ward_id, effective_from, created_at) 
                VALUES (?, ?, NOW(), NOW())
            ");
            foreach ($wardIds as $wId) {
                $stmt->execute([$reservedSeatId, (int)$wId]);
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
