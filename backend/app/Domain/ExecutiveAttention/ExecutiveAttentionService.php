<?php

declare(strict_types=1);

namespace AmarMayor\Domain\ExecutiveAttention;

use AmarMayor\Database\DatabaseManager;
use InvalidArgumentException;
use PDO;

class ExecutiveAttentionService
{
    /**
     * Checks if a complaint has breached its deadline and immediately triggers Mayor / Administrator attention.
     */
    public function checkAndTriggerOverdue(int $complaintId): bool
    {
        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("
            SELECT id, internal_status, citizen_status, deadline_at, deadline_missed_at, closed_at 
            FROM complaints 
            WHERE id = ? 
            LIMIT 1
        ");
        $stmt->execute([$complaintId]);
        $complaint = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$complaint || empty($complaint['deadline_at'])) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        $isOverdue = ($complaint['deadline_at'] < $now && empty($complaint['closed_at']) && !in_array($complaint['internal_status'], ['closed', 'rejected', 'cancelled'], true));

        if (!$isOverdue) {
            return false;
        }

        $pdo->beginTransaction();
        try {
            // Set first deadline missed timestamp (if not already set)
            $pdo->prepare("
                UPDATE complaints
                SET deadline_missed_at = COALESCE(deadline_missed_at, NOW())
                WHERE id = ?
            ")->execute([$complaintId]);

            // Check if active deadline_breach attention already exists
            $checkStmt = $pdo->prepare("
                SELECT id FROM executive_attention 
                WHERE complaint_id = ? AND trigger_type = 'deadline_breach' AND is_active = 1 
                LIMIT 1
            ");
            $checkStmt->execute([$complaintId]);
            $existingId = $checkStmt->fetchColumn();

            if (!$existingId) {
                // Insert Immediate Mayor/Administrator Executive Attention trigger
                $eaStmt = $pdo->prepare("
                    INSERT INTO executive_attention (complaint_id, trigger_type, severity, is_active, created_at)
                    VALUES (?, 'deadline_breach', 'p1_critical', 1, NOW())
                ");
                $eaStmt->execute([$complaintId]);

                // Append status history for transparency
                $histStmt = $pdo->prepare("
                    INSERT INTO complaint_status_history (
                        complaint_id, from_internal_status, to_internal_status,
                        from_citizen_status, to_citizen_status, action_name, reason, created_at
                    ) VALUES (?, ?, ?, ?, ?, 'deadline_breached', 'SLA target exceeded; executive attention triggered', NOW())
                ");
                $histStmt->execute([
                    $complaintId,
                    $complaint['internal_status'],
                    $complaint['internal_status'],
                    $complaint['citizen_status'],
                    $complaint['citizen_status'],
                ]);
            }

            $pdo->commit();
            return true;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Scans all active unclosed complaints past their deadline and triggers executive attention.
     */
    public function scanOverdueComplaints(): int
    {
        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->query("
            SELECT id FROM complaints 
            WHERE deadline_at IS NOT NULL 
              AND deadline_at < NOW() 
              AND closed_at IS NULL 
              AND internal_status NOT IN ('closed', 'rejected', 'cancelled')
        ");
        $overdueIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $triggeredCount = 0;
        foreach ($overdueIds as $id) {
            if ($this->checkAndTriggerOverdue((int)$id)) {
                $triggeredCount++;
            }
        }

        return $triggeredCount;
    }

    /**
     * Returns the active Attention Required queue for the Mayor / Administrator Command Center.
     */
    public function getAttentionQueue(?string $triggerType = null): array
    {
        $pdo = DatabaseManager::getConnection();
        $query = "
            SELECT ea.id as attention_id, ea.trigger_type, ea.severity, ea.created_at as triggered_at,
                   c.id as complaint_id, c.public_complaint_number, c.internal_status, c.citizen_status,
                   c.submitted_at, c.deadline_at, c.reopen_count, c.completion_attempts,
                   cc.name_bn as category_name_bn, cc.name_en as category_name_en,
                   cs.name_bn as subcategory_name_bn, cs.name_en as subcategory_name_en,
                   w.ward_number, d.name_bn as dept_name_bn,
                   p.full_name_bn as supervisor_name_bn, p.official_phone as supervisor_phone
            FROM executive_attention ea
            INNER JOIN complaints c ON c.id = ea.complaint_id
            INNER JOIN complaint_categories cc ON cc.id = c.category_id
            INNER JOIN complaint_subcategories cs ON cs.id = c.subcategory_id
            INNER JOIN wards w ON w.id = c.ward_id
            LEFT JOIN departments d ON d.id = c.department_id
            LEFT JOIN employees e ON e.id = c.current_supervisor_employee_id
            LEFT JOIN persons p ON p.id = e.person_id
            WHERE ea.is_active = 1
        ";

        $params = [];
        if ($triggerType !== null) {
            $query .= " AND ea.trigger_type = ?";
            $params[] = $triggerType;
        }

        $query .= " ORDER BY ea.created_at DESC";

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Issues an executive directive from Mayor / Administrator regarding a complaint.
     */
    public function issueDirective(
        int $executiveUserId,
        int $complaintId,
        string $directiveType,
        string $instruction
    ): int {
        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO executive_directives (executive_user_id, complaint_id, directive_type, instruction, status, created_at)
            VALUES (?, ?, ?, ?, 'issued', NOW())
        ");
        $stmt->execute([$executiveUserId, $complaintId, $directiveType, $instruction]);

        return (int)$pdo->lastInsertId();
    }

    /**
     * Issues a formal explanation request from Mayor / Administrator to an officer.
     */
    public function requestExplanation(
        int $executiveUserId,
        int $targetEmployeeId,
        int $complaintId,
        string $question,
        ?string $dueDate = null
    ): int {
        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO explanation_requests (executive_user_id, target_employee_id, complaint_id, question, due_date, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$executiveUserId, $targetEmployeeId, $complaintId, $question, $dueDate]);

        return (int)$pdo->lastInsertId();
    }
}
