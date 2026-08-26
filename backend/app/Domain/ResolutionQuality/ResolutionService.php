<?php

declare(strict_types=1);

namespace AmarMayor\Domain\ResolutionQuality;

use AmarMayor\Database\DatabaseManager;
use InvalidArgumentException;
use PDO;

class ResolutionService
{
    /**
     * Supervisor verifies the completed field work.
     */
    public function supervisorVerify(
        int $complaintId,
        ?int $supervisorUserId = null,
        bool $isSatisfactory = true,
        ?string $notes = null
    ): void {
        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("SELECT id, internal_status, citizen_status FROM complaints WHERE id = ? LIMIT 1");
        $stmt->execute([$complaintId]);
        $complaint = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$complaint) {
            throw new InvalidArgumentException("Complaint ID {$complaintId} not found.");
        }

        $actorId = null;
        if ($supervisorUserId !== null && $supervisorUserId > 0) {
            $uExists = (bool)$pdo->query("SELECT 1 FROM users WHERE id = {$supervisorUserId}")->fetchColumn();
            if ($uExists) {
                $actorId = $supervisorUserId;
            }
        }

        $pdo->beginTransaction();
        try {
            if ($isSatisfactory) {
                // Verified: Transition to awaiting_citizen_confirmation (Citizen status: confirmation_needed)
                $upStmt = $pdo->prepare("
                    UPDATE complaints
                    SET internal_status = 'awaiting_citizen_confirmation',
                        citizen_status = 'confirmation_needed',
                        verified_at = NOW()
                    WHERE id = ?
                ");
                $upStmt->execute([$complaintId]);

                $histStmt = $pdo->prepare("
                    INSERT INTO complaint_status_history (
                        complaint_id, from_internal_status, to_internal_status,
                        from_citizen_status, to_citizen_status, action_name, actor_user_id, reason, created_at
                    ) VALUES (?, ?, 'awaiting_citizen_confirmation', ?, 'confirmation_needed', 'supervisor_verified', ?, ?, NOW())
                ");
                $histStmt->execute([$complaintId, $complaint['internal_status'], $complaint['citizen_status'], $actorId, $notes]);
            } else {
                // Not Satisfactory: Return to in_progress for field rework
                $upStmt = $pdo->prepare("
                    UPDATE complaints
                    SET internal_status = 'in_progress',
                        citizen_status = 'in_progress'
                    WHERE id = ?
                ");
                $upStmt->execute([$complaintId]);

                $histStmt = $pdo->prepare("
                    INSERT INTO complaint_status_history (
                        complaint_id, from_internal_status, to_internal_status,
                        from_citizen_status, to_citizen_status, action_name, actor_user_id, reason, created_at
                    ) VALUES (?, ?, 'in_progress', ?, 'in_progress', 'supervisor_rejected_needs_rework', ?, ?, NOW())
                ");
                $histStmt->execute([$complaintId, $complaint['internal_status'], $complaint['citizen_status'], $actorId, $notes]);
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Citizen confirms resolution or flags 'Not Resolved' / 'Needs More Work'.
     *
     * @param int $complaintId
     * @param int $citizenUserId
     * @param bool $isResolved True for satisfied closure, false for 'Not Resolved'
     * @param int|null $rating 1-5 rating score (if resolved)
     * @param string|null $comment Optional citizen feedback notes
     * @param string|null $unresolvedReason Optional code (e.g. 'incomplete_work', 'poor_quality')
     */
    public function citizenConfirm(
        int $complaintId,
        int $citizenUserId,
        bool $isResolved,
        ?int $rating = null,
        ?string $comment = null,
        ?string $unresolvedReason = null
    ): void {
        $pdo = DatabaseManager::getConnection();
        $stmt = $pdo->prepare("
            SELECT id, citizen_user_id, internal_status, citizen_status, submitted_at, deadline_at,
                   reopen_count, completion_attempts, first_reopened_at
            FROM complaints 
            WHERE id = ? 
            LIMIT 1
        ");
        $stmt->execute([$complaintId]);
        $complaint = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$complaint) {
            throw new InvalidArgumentException("Complaint ID {$complaintId} not found.");
        }

        if ((int)$complaint['citizen_user_id'] !== $citizenUserId) {
            throw new InvalidArgumentException("Only the citizen who submitted the complaint can provide resolution confirmation.");
        }

        $pdo->beginTransaction();
        try {
            if ($isResolved) {
                // 1. Final Closure
                $upStmt = $pdo->prepare("
                    UPDATE complaints
                    SET internal_status = 'closed',
                        citizen_status = 'resolved',
                        citizen_confirmed_at = NOW(),
                        closed_at = NOW()
                    WHERE id = ?
                ");
                $upStmt->execute([$complaintId]);

                // Insert feedback
                $pdo->prepare("
                    INSERT INTO citizen_feedback (complaint_id, citizen_user_id, resolution_confirmation, rating_score, comment, confirmed_at)
                    VALUES (?, ?, 'yes_resolved', ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE resolution_confirmation = 'yes_resolved', rating_score = VALUES(rating_score), comment = VALUES(comment), confirmed_at = NOW()
                ")->execute([$complaintId, $citizenUserId, $rating, $comment]);

                // Append status history
                $histStmt = $pdo->prepare("
                    INSERT INTO complaint_status_history (
                        complaint_id, from_internal_status, to_internal_status,
                        from_citizen_status, to_citizen_status, action_name, actor_user_id, reason, created_at
                    ) VALUES (?, ?, 'closed', ?, 'resolved', 'citizen_confirmed_resolved', ?, ?, NOW())
                ");
                $histStmt->execute([$complaintId, $complaint['internal_status'], $complaint['citizen_status'], $citizenUserId, $comment]);
            } else {
                // 2. Citizen Reports 'Not Resolved' / 'Needs More Work'
                // Hard Rules:
                // - reopen_count increments (+1)
                // - completion_attempts is NOT incremented here (only increments on actual work completion attempts)
                // - original submitted_at NEVER resets
                // - original deadline_at NEVER resets
                // - total case age NEVER resets
                // - operational owner remains unchanged
                // - Immediate Mayor / Administrator Attention Required trigger created
                $newReopenCount = (int)$complaint['reopen_count'] + 1;

                $upStmt = $pdo->prepare("
                    UPDATE complaints
                    SET internal_status = 'needs_more_work',
                        citizen_status = 'needs_more_work',
                        reopen_count = ?,
                        first_reopened_at = COALESCE(first_reopened_at, NOW())
                    WHERE id = ?
                ");
                $upStmt->execute([$newReopenCount, $complaintId]);

                // Insert feedback recording rejection
                $pdo->prepare("
                    INSERT INTO citizen_feedback (complaint_id, citizen_user_id, resolution_confirmation, unresolved_reason_code, comment, confirmed_at)
                    VALUES (?, ?, 'not_resolved', ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE resolution_confirmation = 'not_resolved', unresolved_reason_code = VALUES(unresolved_reason_code), comment = VALUES(comment), confirmed_at = NOW()
                ")->execute([$complaintId, $citizenUserId, $unresolvedReason, $comment]);

                // Append status history
                $histStmt = $pdo->prepare("
                    INSERT INTO complaint_status_history (
                        complaint_id, from_internal_status, to_internal_status,
                        from_citizen_status, to_citizen_status, action_name, actor_user_id, reason, created_at
                    ) VALUES (?, ?, 'needs_more_work', ?, 'needs_more_work', 'citizen_reopened', ?, ?, NOW())
                ");
                $histStmt->execute([$complaintId, $complaint['internal_status'], $complaint['citizen_status'], $citizenUserId, $comment]);

                // Trigger Immediate Mayor / Administrator Executive Attention
                $eaStmt = $pdo->prepare("
                    INSERT INTO executive_attention (complaint_id, trigger_type, severity, is_active, created_at)
                    VALUES (?, 'citizen_reopen', 'p2_high', 1, NOW())
                ");
                $eaStmt->execute([$complaintId]);
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
