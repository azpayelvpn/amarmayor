<?php

declare(strict_types=1);

namespace AmarMayor\Domain\PublicAccountability;

use AmarMayor\Database\DatabaseManager;
use PDO;

class PublicAccountabilityService
{
    /**
     * Institution-first public accountability metrics.
     * Does NOT collapse all categories into "Solved", shows negative & pending metrics honestly.
     */
    public function getPublicMetrics(): array
    {
        $pdo = DatabaseManager::getConnection();

        $stmt = $pdo->query("
            SELECT
                COUNT(*) as total_received,
                SUM(CASE WHEN internal_status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN internal_status = 'work_completed' THEN 1 ELSE 0 END) as work_completed,
                SUM(CASE WHEN internal_status = 'awaiting_citizen_confirmation' THEN 1 ELSE 0 END) as supervisor_verified,
                SUM(CASE WHEN internal_status = 'closed' THEN 1 ELSE 0 END) as citizen_confirmed_resolved,
                SUM(CASE WHEN internal_status = 'needs_more_work' THEN 1 ELSE 0 END) as needs_more_work,
                SUM(CASE WHEN deadline_at IS NOT NULL AND deadline_at < NOW() AND closed_at IS NULL THEN 1 ELSE 0 END) as currently_overdue,
                SUM(CASE WHEN closed_at IS NOT NULL AND deadline_at IS NOT NULL AND closed_at > deadline_at THEN 1 ELSE 0 END) as late_completions
            FROM complaints
        ");
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'total_received' => (int)($row['total_received'] ?? 0),
            'in_progress' => (int)($row['in_progress'] ?? 0),
            'work_completed' => (int)($row['work_completed'] ?? 0),
            'supervisor_verified' => (int)($row['supervisor_verified'] ?? 0),
            'citizen_confirmed_resolved' => (int)($row['citizen_confirmed_resolved'] ?? 0),
            'needs_more_work' => (int)($row['needs_more_work'] ?? 0),
            'currently_overdue' => (int)($row['currently_overdue'] ?? 0),
            'late_completions' => (int)($row['late_completions'] ?? 0),
        ];
    }

    /**
     * 'Who is Responsible?' civic governance directory by Ward.
     * Displays approved representative names, designations, and official contact numbers.
     */
    public function getWhoIsResponsible(?int $wardId = null): array
    {
        $pdo = DatabaseManager::getConnection();

        $query = "
            SELECT 
                w.id as ward_id, w.ward_number, w.name_bn as ward_name_bn, w.name_en as ward_name_en,
                z.zone_number, z.name_bn as zone_name_bn, z.name_en as zone_name_en,
                rt.name_bn as role_title_bn, rt.slug as role_slug, rt.name_en as role_title_en,
                p.full_name_bn as official_name_bn, p.full_name_en as official_name_en, p.official_phone, p.official_email,
                ra.verification_status, ra.source_name
            FROM wards w
            LEFT JOIN zones z ON z.id = w.zone_id
            LEFT JOIN representation_areas area ON area.area_type = 'ward' AND area.area_id = w.id
            LEFT JOIN representation_assignments ra ON ra.id = area.representation_assignment_id AND ra.status = 'active'
            LEFT JOIN representation_types rt ON rt.id = ra.representation_type_id
            LEFT JOIN persons p ON p.id = ra.person_id
            WHERE w.status = 'active'
        ";

        $params = [];
        if ($wardId !== null) {
            $query .= " AND w.id = ?";
            $params[] = $wardId;
        }

        $query .= " ORDER BY w.ward_number ASC";

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);

        $raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Group by ward so every ward has structured representation or honest empty representation
        $grouped = [];
        foreach ($raw as $row) {
            $wNo = (int)$row['ward_number'];
            if (!isset($grouped[$wNo])) {
                $grouped[$wNo] = [
                    'ward_id' => $row['ward_id'],
                    'ward_number' => $row['ward_number'],
                    'ward_name_bn' => $row['ward_name_bn'],
                    'ward_name_en' => $row['ward_name_en'],
                    'zone_number' => $row['zone_number'],
                    'zone_name_bn' => $row['zone_name_bn'],
                    'zone_name_en' => $row['zone_name_en'],
                    'general_representation' => null,
                    'reserved_seat_representation' => null,
                ];
            }

            if (!empty($row['official_name_bn'])) {
                $repInfo = [
                    'name_bn' => $row['official_name_bn'],
                    'name_en' => $row['official_name_en'],
                    'role_title' => $row['role_title_bn'],
                    'role_slug' => $row['role_slug'],
                    'phone' => $row['official_phone'],
                    'email' => $row['official_email'],
                ];
                if ($row['role_slug'] === 'reserved_women_councillor') {
                    $grouped[$wNo]['reserved_seat_representation'] = $repInfo;
                } else {
                    $grouped[$wNo]['general_representation'] = $repInfo;
                }
            }
        }

        return array_values($grouped);
    }

    /**
     * Active public city announcements and notices.
     */
    public function getPublicNotices(?string $category = null): array
    {
        $pdo = DatabaseManager::getConnection();

        $query = "
            SELECT id, notice_type, title_bn, title_en, body_bn, body_en, published_at, expires_at
            FROM city_notices
            WHERE is_published = 1 AND (expires_at IS NULL OR expires_at > NOW())
        ";

        $params = [];
        if ($category !== null) {
            $query .= " AND notice_type = ?";
            $params[] = $category;
        }

        $query .= " ORDER BY published_at DESC LIMIT 20";

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
