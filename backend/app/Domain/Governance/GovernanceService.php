<?php

declare(strict_types=1);

namespace AmarMayor\Domain\Governance;

use AmarMayor\Database\DatabaseManager;
use InvalidArgumentException;
use PDO;

class GovernanceService
{
    /**
     * Get active civic leadership / representatives for city, zone, or ward.
     */
    public function getActiveRepresentatives(string $scopeType = 'citywide', ?int $scopeId = null): array
    {
        $pdo = DatabaseManager::getConnection();

        $query = "
            SELECT ra.id as assignment_id, ra.authority_basis, ra.effective_from, ra.effective_to, ra.official_order_no as order_number,
                   p.id as person_id, p.full_name_bn, p.full_name_en, p.official_phone as phone, p.photo_url,
                   rt.slug as role_slug, rt.name_bn as role_name_bn, rt.name_en as role_name_en,
                   area.area_type, area.area_id
            FROM representation_assignments ra
            INNER JOIN persons p ON p.id = ra.person_id
            INNER JOIN representation_types rt ON rt.id = ra.representation_type_id
            LEFT JOIN representation_areas area ON area.representation_assignment_id = ra.id
            WHERE (ra.effective_to IS NULL OR ra.effective_to >= NOW())
              AND ra.effective_from <= NOW()
        ";

        $params = [];
        if ($scopeType === 'ward' && $scopeId !== null) {
            $query .= " AND (area.area_type = 'ward' AND area.area_id = ?)";
            $params[] = $scopeId;
        } elseif ($scopeType === 'zone' && $scopeId !== null) {
            $query .= " AND ((area.area_type = 'zone' AND area.area_id = ?) OR (area.area_type = 'ward' AND area.area_id IN (SELECT id FROM wards WHERE zone_id = ?)))";
            $params[] = $scopeId;
            $params[] = $scopeId;
        } elseif ($scopeType === 'citywide') {
            $query .= " AND (area.area_type = 'citywide' OR rt.slug IN ('mayor', 'administrator', 'ceo'))";
        }

        $query .= " ORDER BY rt.id ASC, ra.effective_from DESC";

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Assign a person to a governance representation tenure.
     */
    public function assignRepresentative(array $data): int
    {
        $pdo = DatabaseManager::getConnection();

        // 1. Resolve or create person profile
        $personId = 0;
        if (!empty($data['person_id'])) {
            $personId = (int)$data['person_id'];
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO persons (full_name_bn, full_name_en, official_phone, user_id, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $data['full_name_bn'],
                $data['full_name_en'],
                $data['phone'] ?? null,
                $data['user_id'] ?? null,
            ]);
            $personId = (int)$pdo->lastInsertId();
        }

        // 2. Resolve representation type
        $typeStmt = $pdo->prepare("SELECT id FROM representation_types WHERE slug = ? LIMIT 1");
        $typeStmt->execute([$data['representation_type_slug']]);
        $typeId = (int)$typeStmt->fetchColumn();

        if (!$typeId) {
            throw new InvalidArgumentException("Unknown representation type: {$data['representation_type_slug']}");
        }

        $pdo->beginTransaction();
        try {
            // 3. Create representation assignment
            $stmt = $pdo->prepare("
                INSERT INTO representation_assignments (person_id, representation_type_id, authority_basis, effective_from, effective_to, official_order_no, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $personId,
                $typeId,
                $data['authority_basis'] ?? 'appointed',
                $data['effective_from'],
                $data['effective_to'] ?? null,
                $data['order_number'] ?? null,
            ]);
            $assignmentId = (int)$pdo->lastInsertId();

            // 4. Map representation areas
            if (!empty($data['ward_ids'])) {
                $areaStmt = $pdo->prepare("INSERT INTO representation_areas (representation_assignment_id, area_type, area_id) VALUES (?, 'ward', ?)");
                foreach ($data['ward_ids'] as $wId) {
                    $areaStmt->execute([$assignmentId, (int)$wId]);
                }
            } elseif (!empty($data['zone_id'])) {
                $pdo->prepare("INSERT INTO representation_areas (representation_assignment_id, area_type, area_id) VALUES (?, 'zone', ?)")
                    ->execute([$assignmentId, (int)$data['zone_id']]);
            } elseif (!empty($data['city_id'])) {
                $pdo->prepare("INSERT INTO representation_areas (representation_assignment_id, area_type, area_id) VALUES (?, 'citywide', ?)")
                    ->execute([$assignmentId, (int)$data['city_id']]);
            }

            $pdo->commit();
            return $assignmentId;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * End an active representation tenure.
     */
    public function endRepresentativeTenure(int $assignmentId, string $endDate, ?string $orderNumber = null): void
    {
        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("
            UPDATE representation_assignments 
            SET effective_to = ?, 
                official_order_no = COALESCE(?, official_order_no)
            WHERE id = ?
        ");
        $stmt->execute([$endDate, $orderNumber, $assignmentId]);
    }

    /**
     * Get historical and current timeline of all representatives for a ward.
     */
    public function getRepresentativeHistory(int $wardId): array
    {
        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("
            SELECT ra.id as assignment_id, ra.authority_basis, ra.effective_from, ra.effective_to, ra.official_order_no as order_number,
                   p.full_name_bn, p.full_name_en, p.official_phone as phone, p.photo_url,
                   rt.slug as role_slug, rt.name_bn as role_name_bn, rt.name_en as role_name_en
            FROM representation_areas area
            INNER JOIN representation_assignments ra ON ra.id = area.representation_assignment_id
            INNER JOIN persons p ON p.id = ra.person_id
            INNER JOIN representation_types rt ON rt.id = ra.representation_type_id
            WHERE area.area_type = 'ward' AND area.area_id = ?
            ORDER BY ra.effective_from DESC, ra.id DESC
        ");
        $stmt->execute([$wardId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
