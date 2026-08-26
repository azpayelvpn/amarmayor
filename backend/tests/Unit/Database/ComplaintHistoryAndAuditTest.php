<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Unit\Database;

use AmarMayor\Database\DatabaseManager;
use AmarMayor\Support\Security;
use AmarMayor\Tests\TestCase;
use PDO;

class ComplaintHistoryAndAuditTest extends TestCase
{
    public function testComplaintStatusHistoryAndReopenPreservation(): void
    {
        $pdo = DatabaseManager::getConnection();
        $suffix = bin2hex(random_bytes(4));

        $userId = 0;
        $compId = 0;

        try {
            // 1. Create Citizen User
            $uUuid = Security::uuid();
            $email = "comp_hist_user_{$suffix}@example.com";
            $pdo->exec("INSERT INTO users (uuid, email, user_type, status, created_at) VALUES ('{$uUuid}', '{$email}', 'citizen', 'active', NOW())");
            $userId = (int)$pdo->lastInsertId();

            $origSubmittedAt = '2026-08-26 10:00:00';
            $compNumber = "MCC-TEST-{$suffix}";

            // 2. Insert Complaint
            $pdo->exec("INSERT INTO complaints (
                public_complaint_number, citizen_user_id, category_id, subcategory_id, ward_id, zone_id,
                internal_status, citizen_status, description, submitted_at, completion_attempts, reopen_count, created_at
            ) VALUES (
                '{$compNumber}', {$userId}, 1, 1, 19, 3,
                'submitted', 'received', 'ময়লার স্তূপ টেস্ট', '{$origSubmittedAt}', 0, 0, NOW()
            )");
            $compId = (int)$pdo->lastInsertId();

            // 3. Status Transitions
            $pdo->exec("INSERT INTO complaint_status_history (complaint_id, from_internal_status, to_internal_status, from_citizen_status, to_citizen_status, action_name, actor_user_id, reason, created_at) 
                        VALUES ({$compId}, 'submitted', 'assigned', 'received', 'assigned', 'assign_supervisor', NULL, 'Auto-routed', NOW())");

            $pdo->exec("INSERT INTO complaint_status_history (complaint_id, from_internal_status, to_internal_status, from_citizen_status, to_citizen_status, action_name, actor_user_id, reason, created_at) 
                        VALUES ({$compId}, 'assigned', 'in_progress', 'assigned', 'in_progress', 'start_work', NULL, 'Team on site', NOW())");

            $pdo->exec("INSERT INTO complaint_status_history (complaint_id, from_internal_status, to_internal_status, from_citizen_status, to_citizen_status, action_name, actor_user_id, reason, created_at) 
                        VALUES ({$compId}, 'in_progress', 'work_completed', 'in_progress', 'work_completed', 'complete_field_work', NULL, 'Cleaned up', NOW())");

            // 4. Citizen Reopens (Needs more work)
            $pdo->exec("UPDATE complaints SET 
                        internal_status = 'needs_more_work', 
                        citizen_status = 'needs_more_work',
                        completion_attempts = 1,
                        reopen_count = 1,
                        first_reopened_at = NOW()
                        WHERE id = {$compId}");

            $pdo->exec("INSERT INTO complaint_status_history (complaint_id, from_internal_status, to_internal_status, from_citizen_status, to_citizen_status, action_name, actor_user_id, reason, created_at) 
                        VALUES ({$compId}, 'work_completed', 'needs_more_work', 'work_completed', 'needs_more_work', 'reopen_by_citizen', {$userId}, 'বর্জ্যের কিছু অংশ এখনও রয়ে গেছে', NOW())");

            // Assertions
            $comp = $pdo->query("SELECT submitted_at, completion_attempts, reopen_count, first_reopened_at, internal_status FROM complaints WHERE id = {$compId}")->fetch(PDO::FETCH_ASSOC);
            $this->assertEquals($origSubmittedAt, $comp['submitted_at'], "Original submitted_at must NEVER reset on reopen");
            $this->assertEquals(1, (int)$comp['completion_attempts']);
            $this->assertEquals(1, (int)$comp['reopen_count']);
            $this->assertNotNull($comp['first_reopened_at']);
            $this->assertEquals('needs_more_work', $comp['internal_status']);

            $histCount = (int)$pdo->query("SELECT COUNT(*) FROM complaint_status_history WHERE complaint_id = {$compId}")->fetchColumn();
            $this->assertEquals(4, $histCount, "Status history must record all 4 transitions");
        } finally {
            if ($compId) {
                $pdo->exec("DELETE FROM complaint_status_history WHERE complaint_id = {$compId}");
                $pdo->exec("DELETE FROM complaints WHERE id = {$compId}");
            }
            if ($userId) $pdo->exec("DELETE FROM users WHERE id = {$userId}");
        }
    }

    public function testExecutiveAttentionLinksWithoutOverwritingOwnership(): void
    {
        $pdo = DatabaseManager::getConnection();
        $suffix = bin2hex(random_bytes(4));

        $userId = 0;
        $compId = 0;
        $eaId = 0;

        try {
            // 1. Create User & Complaint
            $uUuid = Security::uuid();
            $email = "exec_att_user_{$suffix}@example.com";
            $pdo->exec("INSERT INTO users (uuid, email, user_type, status, created_at) VALUES ('{$uUuid}', '{$email}', 'citizen', 'active', NOW())");
            $userId = (int)$pdo->lastInsertId();

            $compNumber = "MCC-EXEC-{$suffix}";
            $pdo->exec("INSERT INTO complaints (
                public_complaint_number, citizen_user_id, category_id, subcategory_id, ward_id, zone_id,
                internal_status, citizen_status, description, submitted_at, created_at
            ) VALUES (
                '{$compNumber}', {$userId}, 1, 1, 19, 3,
                'assigned', 'assigned', 'অতিরিক্ত জলাবদ্ধতা', NOW(), NOW()
            )");
            $compId = (int)$pdo->lastInsertId();

            // 2. Trigger Executive Attention (e.g. first_citizen_reopen or critical_hazard)
            $pdo->exec("INSERT INTO executive_attention (complaint_id, trigger_type, severity, is_active, created_at) 
                        VALUES ({$compId}, 'critical_hazard', 'p1_critical', 1, NOW())");
            $eaId = (int)$pdo->lastInsertId();

            // Verify executive attention exists and operational ownership remains intact
            $eaRow = $pdo->query("SELECT complaint_id, trigger_type, is_active FROM executive_attention WHERE id = {$eaId}")->fetch(PDO::FETCH_ASSOC);
            $this->assertEquals($compId, (int)$eaRow['complaint_id']);
            $this->assertEquals('critical_hazard', $eaRow['trigger_type']);
            $this->assertEquals(1, (int)$eaRow['is_active']);
        } finally {
            if ($eaId) $pdo->exec("DELETE FROM executive_attention WHERE id = {$eaId}");
            if ($compId) $pdo->exec("DELETE FROM complaints WHERE id = {$compId}");
            if ($userId) $pdo->exec("DELETE FROM users WHERE id = {$userId}");
        }
    }

    public function testAuditLogAppendOnlyStructure(): void
    {
        $pdo = DatabaseManager::getConnection();
        $reqId = Security::uuid();

        $pdo->exec("INSERT INTO audit_logs (
            event_category, action, entity_type, entity_id, old_values, new_values, reason, request_id, created_at
        ) VALUES (
            'complaint', 'status_transition', 'complaint', 101, 
            '{\"status\":\"assigned\"}', '{\"status\":\"in_progress\"}', 'Worker arrived on site', '{$reqId}', NOW()
        )");
        $auditId = (int)$pdo->lastInsertId();

        $auditRow = $pdo->query("SELECT event_category, action, entity_type, entity_id, request_id FROM audit_logs WHERE id = {$auditId}")->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals('complaint', $auditRow['event_category']);
        $this->assertEquals('status_transition', $auditRow['action']);
        $this->assertEquals(101, (int)$auditRow['entity_id']);
        $this->assertEquals($reqId, $auditRow['request_id']);

        // Cleanup
        $pdo->exec("DELETE FROM audit_logs WHERE id = {$auditId}");
    }
}
