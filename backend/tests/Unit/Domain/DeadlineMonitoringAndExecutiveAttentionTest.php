<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Unit\Domain;

use AmarMayor\Database\DatabaseManager;
use AmarMayor\Domain\ComplaintCore\ComplaintService;
use AmarMayor\Domain\ExecutiveAttention\ExecutiveAttentionService;
use AmarMayor\Tests\TestCase;
use PDO;

class DeadlineMonitoringAndExecutiveAttentionTest extends TestCase
{
    private ComplaintService $complaintService;
    private ExecutiveAttentionService $executiveService;

    public function setUp(): void
    {
        parent::setUp();
        $this->complaintService = new ComplaintService();
        $this->executiveService = new ExecutiveAttentionService();
    }

    public function testOverdueTriggersImmediateExecutiveAttentionWithoutEscalationLadder(): void
    {
        $pdo = DatabaseManager::getConnection();

        // 1. Create Citizen & Executive User (Mayor)
        $existing = $pdo->query("SELECT id FROM users WHERE uuid IN ('test-citizen-dm1', 'test-mayor-dm1')")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($existing as $exId) {
            $complaintIds = $pdo->query("SELECT id FROM complaints WHERE citizen_user_id = {$exId}")->fetchAll(PDO::FETCH_COLUMN);
            foreach ($complaintIds as $cid) {
                $pdo->exec("DELETE FROM internal_notes WHERE complaint_id = {$cid}");
                $pdo->exec("DELETE FROM executive_directives WHERE complaint_id = {$cid}");
                $pdo->exec("DELETE FROM executive_attention WHERE complaint_id = {$cid}");
                $pdo->exec("DELETE FROM complaint_status_history WHERE complaint_id = {$cid}");
                $pdo->exec("DELETE FROM complaint_locations WHERE complaint_id = {$cid}");
                $pdo->exec("DELETE FROM complaints WHERE id = {$cid}");
            }
            $pdo->exec("DELETE FROM users WHERE id = {$exId}");
        }

        $stmt = $pdo->prepare("INSERT INTO users (uuid, phone, user_type, status, preferred_language, created_at) VALUES ('test-citizen-dm1', '+8801711000006', 'citizen', 'active', 'bn', NOW())");
        $stmt->execute();
        $citizenUserId = (int)$pdo->lastInsertId();

        $mStmt = $pdo->prepare("INSERT INTO users (uuid, phone, user_type, status, preferred_language, created_at) VALUES ('test-mayor-dm1', '+8801711000007', 'executive', 'active', 'bn', NOW())");
        $mStmt->execute();
        $mayorUserId = (int)$pdo->lastInsertId();

        $catId = (int)$pdo->query("SELECT id FROM complaint_categories WHERE slug = 'cleanliness'")->fetchColumn();
        $subId = (int)$pdo->query("SELECT id FROM complaint_subcategories WHERE slug = 'medical_waste'")->fetchColumn();
        $wardId = (int)$pdo->query("SELECT id FROM wards WHERE ward_number = 1")->fetchColumn();
        $deptId = (int)$pdo->query("SELECT id FROM departments WHERE slug = 'waste_management'")->fetchColumn();

        $complaintId = 0;
        try {
            // 2. Submit Complaint
            $complaint = $this->complaintService->createComplaint([
                'citizen_user_id' => $citizenUserId,
                'category_id' => $catId,
                'subcategory_id' => $subId,
                'ward_id' => $wardId,
                'description' => 'হাসপাতালের সামনে অনিরাপদ মেডিকেল বর্জ্য।',
            ]);
            $complaintId = (int)$complaint['id'];

            // 3. Manually simulate an SLA deadline that passed 2 hours ago
            $pastDeadline = date('Y-m-d H:i:s', strtotime('-2 hours'));
            $pdo->prepare("UPDATE complaints SET department_id = ?, deadline_at = ? WHERE id = ?")
                ->execute([$deptId, $pastDeadline, $complaintId]);

            // 4. Overdue Scanner runs
            $triggered = $this->executiveService->checkAndTriggerOverdue($complaintId);
            $this->assertTrue($triggered, "Scanner must detect deadline breach");

            // 5. Verify Executive Attention created immediately
            $attentionItems = $this->executiveService->getAttentionQueue('deadline_breach');
            $found = false;
            foreach ($attentionItems as $item) {
                if ((int)$item['complaint_id'] === $complaintId) {
                    $found = true;
                    $this->assertEquals('p1_critical', $item['severity']);
                    $this->assertEquals('deadline_breach', $item['trigger_type']);
                    break;
                }
            }
            $this->assertTrue($found, "Overdue complaint must be present in Mayor / Administrator attention queue");

            // 6. Verify Operational Department Ownership remains intact (NO automated reassignment!)
            $cCheck = $this->complaintService->getComplaintById($complaintId);
            $this->assertEquals($deptId, (int)$cCheck['department_id'], "Operational department ownership must remain with responsible unit");
            $this->assertNotNull($cCheck['deadline_missed_at']);

            // 7. Mayor Issues a Binding Executive Directive
            $directiveId = $this->executiveService->issueDirective(
                $mayorUserId,
                $complaintId,
                'immediate_action',
                '২ ঘণ্টার মধ্যে বিশেষ মেডিকেল বর্জ্য ভ্যান পাঠিয়ে অপসারণ নিশ্চিত করুন।'
            );
            $this->assert($directiveId > 0);

            $dRow = $pdo->query("SELECT * FROM executive_directives WHERE id = {$directiveId}")->fetch(PDO::FETCH_ASSOC);
            $this->assertNotNull($dRow);
            $this->assertEquals('issued', $dRow['status']);
        } finally {
            if ($complaintId) {
                $pdo->exec("DELETE FROM internal_notes WHERE complaint_id = {$complaintId}");
                $pdo->exec("DELETE FROM executive_directives WHERE complaint_id = {$complaintId}");
                $pdo->exec("DELETE FROM executive_attention WHERE complaint_id = {$complaintId}");
                $pdo->exec("DELETE FROM complaint_status_history WHERE complaint_id = {$complaintId}");
                $pdo->exec("DELETE FROM complaint_locations WHERE complaint_id = {$complaintId}");
                $pdo->exec("DELETE FROM complaints WHERE id = {$complaintId}");
            }
            $pdo->exec("DELETE FROM users WHERE id IN ({$citizenUserId}, {$mayorUserId})");
        }
    }
}
