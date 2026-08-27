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
     * 'Who is Responsible?' civic governance and operational directory by Ward.
     * Displays approved representative names, designations, operational MCC officers,
     * leave-time substitutes, and verified source-published contact numbers.
     */
    public function getWhoIsResponsible(?int $wardId = null): array
    {
        $pdo = DatabaseManager::getConnection();

        // 1. Fetch Ward structures & Governance Representatives
        $govQuery = "
            SELECT 
                w.id as ward_id, w.ward_number, w.name_bn as ward_name_bn, w.name_en as ward_name_en,
                z.zone_number, z.name_bn as zone_name_bn, z.name_en as zone_name_en,
                rt.name_bn as role_title_bn, rt.slug as role_slug, rt.name_en as role_title_en,
                p.full_name_bn as official_name_bn, p.full_name_en as official_name_en, p.official_phone, p.official_email,
                p.status_note as person_status_note,
                ra.authority_basis, ra.raw_source_title, ra.status_note as ra_status_note,
                ra.verification_status as ra_verification_status, ra.source_name as ra_source_name,
                emp.designation_bn as official_designation_bn
            FROM wards w
            LEFT JOIN zones z ON z.id = w.zone_id
            LEFT JOIN representation_areas area ON area.area_type = 'ward' AND area.area_id = w.id
            LEFT JOIN representation_assignments ra ON ra.id = area.representation_assignment_id AND ra.status = 'active'
                AND ra.is_demo = 0 AND ra.effective_from <= NOW() AND (ra.effective_to IS NULL OR ra.effective_to >= NOW())
            LEFT JOIN representation_types rt ON rt.id = ra.representation_type_id
            LEFT JOIN persons p ON p.id = ra.person_id
            LEFT JOIN employees emp ON emp.person_id = p.id AND emp.is_demo = 0
            WHERE w.status = 'active'
        ";

        $targetWardId = null;
        if ($wardId !== null) {
            $wStmtCheck = $pdo->prepare("SELECT id FROM wards WHERE id = ? OR ward_number = ? LIMIT 1");
            $wStmtCheck->execute([$wardId, $wardId]);
            $fetched = $wStmtCheck->fetchColumn();
            $targetWardId = $fetched !== false ? (int)$fetched : $wardId;
        }

        $govParams = [];
        if ($targetWardId !== null) {
            $govQuery .= " AND w.id = ?";
            $govParams[] = $targetWardId;
        }

        $govQuery .= " ORDER BY w.ward_number ASC";

        $govStmt = $pdo->prepare($govQuery);
        $govStmt->execute($govParams);
        $govRows = $govStmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Fetch Operational Ward Officers & Leave Substitutes
        $opQuery = "
            SELECT 
                er.area_id as ward_id,
                p_pri.full_name_bn as op_name_bn, p_pri.full_name_en as op_name_en, p_pri.official_phone as op_phone,
                e_pri.designation_bn as op_designation_bn, e_pri.designation_en as op_designation_en,
                e_pri.employee_code as op_code, e_pri.official_service_no as op_pid,
                p_sub.full_name_bn as sub_name_bn, p_sub.full_name_en as sub_name_en, p_sub.official_phone as sub_phone,
                e_sub.designation_bn as sub_designation_bn, e_sub.designation_en as sub_designation_en,
                e_sub.employee_code as sub_code, e_sub.official_service_no as sub_pid,
                er.responsibility_type, er.raw_source_title as op_raw_source_title, er.verification_status as op_verification_status
            FROM employee_responsibilities er
            INNER JOIN employees e_pri ON e_pri.id = er.employee_id
            INNER JOIN persons p_pri ON p_pri.id = e_pri.person_id
            LEFT JOIN employees e_sub ON e_sub.id = er.substitute_employee_id
            LEFT JOIN persons p_sub ON p_sub.id = e_sub.person_id
            WHERE er.area_type = 'ward' AND er.is_demo = 0 AND er.responsibility_type = 'primary'
              AND er.effective_from <= NOW() AND (er.effective_to IS NULL OR er.effective_to >= NOW())
        ";

        $opParams = [];
        if ($targetWardId !== null) {
            $opQuery .= " AND er.area_id = ?";
            $opParams[] = $targetWardId;
        }

        $opStmt = $pdo->prepare($opQuery);
        $opStmt->execute($opParams);
        $opRows = $opStmt->fetchAll(PDO::FETCH_ASSOC);

        $opByWard = [];
        foreach ($opRows as $op) {
            $wId = (int)$op['ward_id'];
            $substitute = null;
            if (!empty($op['sub_name_bn'])) {
                $substitute = [
                    'name_bn' => $op['sub_name_bn'],
                    'name_en' => $op['sub_name_en'],
                    'designation_bn' => $op['sub_designation_bn'],
                    'designation_en' => $op['sub_designation_en'],
                    'employee_code' => $op['sub_code'],
                    'personnel_id' => $op['sub_pid'],
                    'phone' => $op['sub_phone'],
                ];
            }

            $opByWard[$wId] = [
                'name_bn' => $op['op_name_bn'],
                'name_en' => $op['op_name_en'],
                'designation_bn' => $op['op_designation_bn'],
                'designation_en' => $op['op_designation_en'],
                'employee_code' => $op['op_code'],
                'personnel_id' => $op['op_pid'],
                'phone' => $op['op_phone'],
                'raw_source_title' => $op['op_raw_source_title'],
                'verification_status' => $op['op_verification_status'],
                'substitute' => $substitute,
            ];
        }

        // Group by ward
        $grouped = [];
        foreach ($govRows as $row) {
            $wNo = (int)$row['ward_number'];
            $wId = (int)$row['ward_id'];

            if (!isset($grouped[$wNo])) {
                $grouped[$wNo] = [
                    'ward_id' => $wId,
                    'ward_number' => $row['ward_number'],
                    'ward_name_bn' => $row['ward_name_bn'],
                    'ward_name_en' => $row['ward_name_en'],
                    'zone_number' => $row['zone_number'],
                    'zone_name_bn' => $row['zone_name_bn'],
                    'zone_name_en' => $row['zone_name_en'],
                    'general_representation' => null,
                    'reserved_seat_representation' => null,
                    'operational_responsibility' => $opByWard[$wId] ?? null,
                ];
            }

            if (!empty($row['official_name_bn'])) {
                $statusNote = $row['ra_status_note'] ?: $row['person_status_note'];
                $repInfo = [
                    'name_bn' => $row['official_name_bn'],
                    'name_en' => $row['official_name_en'],
                    'role_title' => $row['role_title_bn'],
                    'role_title_bn' => $row['role_title_bn'],
                    'role_title_en' => $row['role_title_en'],
                    'role_slug' => $row['role_slug'],
                    'authority_basis' => $row['authority_basis'] ?? 'appointed',
                    'designation' => $row['official_designation_bn'] ?: $row['role_title_bn'],
                    'phone' => $row['official_phone'],
                    'email' => $row['official_email'],
                    'raw_source_title' => $row['raw_source_title'],
                    'status_note' => $statusNote,
                    'verification_status' => $row['ra_verification_status'],
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
