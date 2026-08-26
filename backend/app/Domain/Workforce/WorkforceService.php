<?php

declare(strict_types=1);

namespace AmarMayor\Domain\Workforce;

use AmarMayor\Database\DatabaseManager;
use InvalidArgumentException;
use PDO;

class WorkforceService
{
    /**
     * Get all departments with their respective service units.
     */
    public function getDepartments(bool $includeServiceUnits = true): array
    {
        $pdo = DatabaseManager::getConnection();
        $departments = $pdo->query("
            SELECT id, slug, name_bn, name_en, description_bn, description_en, status 
            FROM departments 
            WHERE status = 'active' 
            ORDER BY id ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        if (!$includeServiceUnits) {
            return $departments;
        }

        foreach ($departments as &$dept) {
            $stmt = $pdo->prepare("
                SELECT id, slug, name_bn, name_en, status 
                FROM service_units 
                WHERE department_id = ? AND status = 'active' 
                ORDER BY id ASC
            ");
            $stmt->execute([$dept['id']]);
            $dept['service_units'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $departments;
    }

    /**
     * Create an employee profile with posting and skills.
     */
    public function createEmployee(array $data): int
    {
        $pdo = DatabaseManager::getConnection();

        // 1. Resolve or create Person profile
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

        $pdo->beginTransaction();
        try {
            // 2. Insert into employees
            $stmt = $pdo->prepare("
                INSERT INTO employees (person_id, employee_code, designation_bn, designation_en, employment_type, duty_status, reports_to_employee_id, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $personId,
                $data['employee_code'],
                $data['designation_bn'],
                $data['designation_en'],
                $data['employment_type'] ?? 'permanent',
                $data['duty_status'] ?? 'available',
                $data['supervisor_id'] ?? null,
            ]);
            $employeeId = (int)$pdo->lastInsertId();

            // 3. Create initial posting
            $postingStmt = $pdo->prepare("
                INSERT INTO employee_postings (employee_id, department_id, service_unit_id, transfer_order_ref, effective_from, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $postingStmt->execute([
                $employeeId,
                $data['department_id'],
                $data['service_unit_id'] ?? null,
                $data['posting_order_number'] ?? null,
                $data['effective_from'] ?? date('Y-m-d H:i:s'),
            ]);

            // 4. Attach skills
            if (!empty($data['skill_ids'])) {
                $skillStmt = $pdo->prepare("INSERT INTO employee_skills (employee_id, skill_id) VALUES (?, ?)");
                foreach ($data['skill_ids'] as $sId) {
                    $skillStmt->execute([$employeeId, (int)$sId]);
                }
            }

            $pdo->commit();
            return $employeeId;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Update employee real-time duty status (available, on_duty, off_duty, on_leave, suspended).
     */
    public function updateEmployeeDutyStatus(int $employeeId, string $status): void
    {
        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("UPDATE employees SET duty_status = ? WHERE id = ?");
        $stmt->execute([$status, $employeeId]);
    }

    /**
     * Transfer an employee to a new posting.
     */
    public function transferEmployee(
        int $employeeId,
        int $newDepartmentId,
        ?int $newServiceUnitId,
        ?int $newWardId,
        ?int $newZoneId,
        string $orderNumber,
        string $effectiveFrom
    ): void {
        $pdo = DatabaseManager::getConnection();
        $pdo->beginTransaction();
        try {
            // Close existing active posting
            $stmt = $pdo->prepare("
                UPDATE employee_postings 
                SET effective_to = ? 
                WHERE employee_id = ? AND effective_to IS NULL
            ");
            $stmt->execute([$effectiveFrom, $employeeId]);

            // Create new posting
            $newStmt = $pdo->prepare("
                INSERT INTO employee_postings (employee_id, department_id, service_unit_id, transfer_order_ref, effective_from, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $newStmt->execute([
                $employeeId,
                $newDepartmentId,
                $newServiceUnitId,
                $orderNumber,
                $effectiveFrom,
            ]);

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * List operational teams with members and geographic coverage.
     */
    public function getTeams(?int $departmentId = null, ?int $wardId = null): array
    {
        $pdo = DatabaseManager::getConnection();
        $query = "
            SELECT t.id, t.name_bn, t.name_en, t.department_id, t.service_unit_id, t.supervisor_employee_id, t.team_leader_employee_id, t.status,
                   d.name_bn as dept_name_bn, d.name_en as dept_name_en,
                   p.full_name_bn as leader_name_bn, p.full_name_en as leader_name_en
            FROM teams t
            INNER JOIN departments d ON d.id = t.department_id
            LEFT JOIN employees e ON e.id = t.team_leader_employee_id
            LEFT JOIN persons p ON p.id = e.person_id
            WHERE t.status = 'active'
        ";

        $params = [];
        if ($departmentId !== null) {
            $query .= " AND t.department_id = ?";
            $params[] = $departmentId;
        }

        if ($wardId !== null) {
            $query .= " AND t.id IN (SELECT team_id FROM team_coverage WHERE area_type = 'ward' AND area_id = ?)";
            $params[] = $wardId;
        }

        $query .= " ORDER BY t.id ASC";

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $teams = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($teams as &$team) {
            // Covered Wards
            $wStmt = $pdo->prepare("
                SELECT w.id, w.ward_number, w.name_bn, w.name_en 
                FROM team_coverage tc
                INNER JOIN wards w ON w.id = tc.area_id
                WHERE tc.team_id = ? AND tc.area_type = 'ward'
                ORDER BY w.ward_number ASC
            ");
            $wStmt->execute([$team['id']]);
            $team['covered_wards'] = $wStmt->fetchAll(PDO::FETCH_ASSOC);

            // Team Members
            $mStmt = $pdo->prepare("
                SELECT e.id as employee_id, e.employee_code, e.designation_bn, e.designation_en, e.duty_status,
                       p.full_name_bn, p.full_name_en, p.official_phone as phone
                FROM team_members tm
                INNER JOIN employees e ON e.id = tm.employee_id
                INNER JOIN persons p ON p.id = e.person_id
                WHERE tm.team_id = ?
            ");
            $mStmt->execute([$team['id']]);
            $team['members'] = $mStmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $teams;
    }

    /**
     * Create an operational team with leader, members, and geographic ward coverage.
     */
    public function createTeam(
        string $nameBn,
        string $nameEn,
        int $departmentId,
        ?int $serviceUnitId,
        ?int $leaderEmployeeId,
        array $memberEmployeeIds,
        array $coveredWardIds
    ): int {
        $pdo = DatabaseManager::getConnection();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("
                INSERT INTO teams (name_bn, name_en, department_id, service_unit_id, team_leader_employee_id, status, created_at)
                VALUES (?, ?, ?, ?, ?, 'active', NOW())
            ");
            $stmt->execute([$nameBn, $nameEn, $departmentId, $serviceUnitId, $leaderEmployeeId]);
            $teamId = (int)$pdo->lastInsertId();

            // Insert Leader as team member if provided
            if ($leaderEmployeeId) {
                $pdo->prepare("INSERT INTO team_members (team_id, employee_id, effective_from, created_at) VALUES (?, ?, NOW(), NOW())")
                    ->execute([$teamId, $leaderEmployeeId]);
            }

            // Insert Members
            $memStmt = $pdo->prepare("INSERT INTO team_members (team_id, employee_id, effective_from, created_at) VALUES (?, ?, NOW(), NOW())");
            foreach ($memberEmployeeIds as $mId) {
                if ($mId !== $leaderEmployeeId) {
                    $memStmt->execute([$teamId, (int)$mId]);
                }
            }

            // Insert Covered Wards
            $covStmt = $pdo->prepare("INSERT INTO team_coverage (team_id, area_type, area_id, effective_from, created_at) VALUES (?, 'ward', ?, NOW(), NOW())");
            foreach ($coveredWardIds as $wId) {
                $covStmt->execute([$teamId, (int)$wId]);
            }

            $pdo->commit();
            return $teamId;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
