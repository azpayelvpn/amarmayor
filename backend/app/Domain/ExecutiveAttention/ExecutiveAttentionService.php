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
            SELECT id, priority, internal_status, citizen_status, deadline_at, deadline_missed_at, closed_at 
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
                // Determine executive attention severity based on complaint priority without altering operational priority
                $severity = match ($complaint['priority'] ?? 'p3_normal') {
                    'p1_critical' => 'p1_critical',
                    'p2_high' => 'p2_high',
                    default => 'p3_normal',
                };

                // Insert Immediate Mayor/Administrator Executive Attention trigger
                $eaStmt = $pdo->prepare("
                    INSERT INTO executive_attention (complaint_id, trigger_type, severity, is_active, created_at)
                    VALUES (?, 'deadline_breach', ?, 1, NOW())
                ");
                $eaStmt->execute([$complaintId, $severity]);

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
            INNER JOIN (
                SELECT MAX(id) as max_ea_id, complaint_id
                FROM executive_attention
                WHERE is_active = 1
                GROUP BY complaint_id
            ) latest_ea ON latest_ea.max_ea_id = ea.id
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
        $directiveId = (int)$pdo->lastInsertId();

        // Check assigned supervisor and notify them immediately
        $supStmt = $pdo->prepare("
            SELECT p.user_id, c.public_complaint_number, w.ward_number
            FROM complaints c
            LEFT JOIN wards w ON w.id = c.ward_id
            LEFT JOIN employees e ON e.id = c.current_supervisor_employee_id
            LEFT JOIN persons p ON p.id = e.person_id
            WHERE c.id = ? LIMIT 1
        ");
        $supStmt->execute([$complaintId]);
        $supInfo = $supStmt->fetch(PDO::FETCH_ASSOC);

        if ($supInfo && !empty($supInfo['user_id'])) {
            try {
                $notif = new \AmarMayor\Domain\Notifications\NotificationService();
                $notif->notifyUser(
                    (int)$supInfo['user_id'],
                    "🚨 মেয়র মহোদয়ের সরাসরি নির্দেশনা!",
                    "Mayor Executive Directive",
                    "অভিযোগ {$supInfo['public_complaint_number']}-এর বিষয়ে মেয়র মহোদয়ের জরুরি নির্দেশনা: {$instruction}",
                    "Mayor directive for {$supInfo['public_complaint_number']}: {$instruction}",
                    "executive_directive",
                    ['complaint_id' => $complaintId, 'directive_id' => $directiveId]
                );
            } catch (\Throwable $t) {
                // Non-blocking notification failure
            }
        }

        // Record into internal notes
        try {
            $noteStmt = $pdo->prepare("
                INSERT INTO internal_notes (complaint_id, author_user_id, note_type, note_text, created_at)
                VALUES (?, ?, 'directive', ?, NOW())
            ");
            $noteStmt->execute([$complaintId, $executiveUserId, "মেয়র মহোদয়ের নির্দেশনা ({$directiveType}): " . $instruction]);
        } catch (\Throwable $t) {
            // Non-blocking note failure
        }

        return $directiveId;
    }

    /**
     * Supervisor responds to Mayor's Executive Directive.
     */
    public function respondToDirective(int $directiveId, int $supervisorUserId, string $responseText): void
    {
        $pdo = DatabaseManager::getConnection();

        $stmt = $pdo->prepare("
            SELECT ed.*, c.public_complaint_number, p.full_name_bn as supervisor_name
            FROM executive_directives ed
            LEFT JOIN complaints c ON c.id = ed.complaint_id
            LEFT JOIN persons p ON p.user_id = ?
            WHERE ed.id = ? LIMIT 1
        ");
        $stmt->execute([$supervisorUserId, $directiveId]);
        $directive = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$directive) {
            throw new InvalidArgumentException("Directive #{$directiveId} not found.");
        }

        $pdo->beginTransaction();
        try {
            $upd = $pdo->prepare("
                UPDATE executive_directives
                SET status = 'acknowledged', response_text = ?, responded_at = NOW()
                WHERE id = ?
            ");
            $upd->execute([$responseText, $directiveId]);

            // Append internal note
            if (!empty($directive['complaint_id'])) {
                $noteStmt = $pdo->prepare("
                    INSERT INTO internal_notes (complaint_id, author_user_id, note_type, note_text, created_at)
                    VALUES (?, ?, 'directive_reply', ?, NOW())
                ");
                $supName = $directive['supervisor_name'] ?? 'সুপারভাইজার';
                $noteStmt->execute([
                    $directive['complaint_id'],
                    $supervisorUserId,
                    "মেয়র মহোদয়ের নির্দেশনার জবাব ({$supName}): " . $responseText
                ]);
            }

            $pdo->commit();

            // Real-time notify Mayor / Executive
            if (!empty($directive['executive_user_id'])) {
                try {
                    $notif = new \AmarMayor\Domain\Notifications\NotificationService();
                    $compNum = $directive['public_complaint_number'] ?? 'সাধারণ';
                    $notif->notifyUser(
                        (int)$directive['executive_user_id'],
                        "নির্দেশনার জবাব এসেছে ({$compNum})",
                        "Supervisor Replied to Directive",
                        "অভিযোগ {$compNum}-এর বিষয়ে সুপারভাইজারের জবাব: {$responseText}",
                        "Supervisor responded to directive on {$compNum}: {$responseText}",
                        "directive_response",
                        ['directive_id' => $directiveId, 'complaint_id' => $directive['complaint_id']]
                    );
                } catch (\Throwable $t) {
                    // Non-blocking notification failure
                }
            }
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Get active executive directives for a supervisor's ward.
     */
    public function getDirectivesForSupervisor(int $supervisorUserId): array
    {
        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("
            SELECT ed.*, c.public_complaint_number, w.ward_number,
                   p_exec.full_name_bn as executive_name_bn
            FROM executive_directives ed
            INNER JOIN complaints c ON c.id = ed.complaint_id
            INNER JOIN wards w ON w.id = c.ward_id
            LEFT JOIN persons p_exec ON p_exec.user_id = ed.executive_user_id
            WHERE ed.status = 'issued' AND c.ward_id IN (
                SELECT w2.id FROM employee_responsibilities er
                INNER JOIN employees em ON em.id = er.employee_id
                INNER JOIN persons pr ON pr.id = em.person_id
                INNER JOIN wards w2 ON w2.ward_number = er.area_id
                WHERE pr.user_id = ? AND er.area_type = 'ward'
            )
            ORDER BY ed.id DESC
        ");
        $stmt->execute([$supervisorUserId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
