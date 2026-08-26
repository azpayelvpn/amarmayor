<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Unit\Domain;

use AmarMayor\Database\DatabaseManager;
use AmarMayor\Domain\Communication\CommunicationService;
use AmarMayor\Domain\PublicAccountability\PublicAccountabilityService;
use AmarMayor\Support\Security;
use AmarMayor\Tests\TestCase;
use PDO;

class CommunicationAndAccountabilityTest extends TestCase
{
    private CommunicationService $communicationService;
    private PublicAccountabilityService $publicService;

    public function setUp(): void
    {
        parent::setUp();
        $this->communicationService = new CommunicationService();
        $this->publicService = new PublicAccountabilityService();
    }

    public function testCommunicationAndOfficeMessages(): void
    {
        $pdo = DatabaseManager::getConnection();

        // 1. Create Test Citizen & Officer
        $stmt = $pdo->prepare("INSERT INTO users (uuid, phone, user_type, status, preferred_language, created_at) VALUES (?, '+8801733000001', 'citizen', 'active', 'bn', NOW())");
        $stmt->execute([Security::uuid()]);
        $citizenUserId = (int)$pdo->lastInsertId();

        $stmt2 = $pdo->prepare("INSERT INTO users (uuid, phone, user_type, status, preferred_language, created_at) VALUES (?, '+8801733000002', 'staff', 'active', 'bn', NOW())");
        $stmt2->execute([Security::uuid()]);
        $officerUserId = (int)$pdo->lastInsertId();

        $catId = (int)$pdo->query("SELECT id FROM complaint_categories LIMIT 1")->fetchColumn();
        $subId = (int)$pdo->query("SELECT id FROM complaint_subcategories LIMIT 1")->fetchColumn();
        $wardId = (int)$pdo->query("SELECT id FROM wards LIMIT 1")->fetchColumn();

        $complaintId = 0;
        try {
            $pdo->prepare("
                INSERT INTO complaints (public_complaint_number, citizen_user_id, category_id, subcategory_id, ward_id, zone_id, description, submitted_at, created_at)
                VALUES ('MCC-TEST-COMM-01', ?, ?, ?, ?, 1, 'যোগাযোগ টেস্ট অভিযোগ', NOW(), NOW())
            ")->execute([$citizenUserId, $catId, $subId, $wardId]);
            $complaintId = (int)$pdo->lastInsertId();

            // 2. Citizen sends message
            $msgId = $this->communicationService->sendComplaintMessage($complaintId, $citizenUserId, 'কখন ড্রেন পরিষ্কার শুরু হবে?');
            $this->assert($msgId > 0);

            // 3. Officer adds internal staff note
            $noteId = $this->communicationService->addInternalNote($complaintId, $officerUserId, 'মাঠ টিমের কাছে সরঞ্জাম পাঠানো হয়েছে।');
            $this->assert($noteId > 0);

            // 4. Fetch messages
            $messages = $this->communicationService->getComplaintMessages($complaintId);
            $this->assertCount(1, $messages);
            $this->assertEquals('কখন ড্রেন পরিষ্কার শুরু হবে?', $messages[0]['body']);

            // 5. Citizen sends Office Message to Mayor
            $officeMsgId = $this->communicationService->sendOfficeMessage($citizenUserId, 'mayor_office', 'general', 'পৌর পার্কের বিষয়ে একটি আবেদন।');
            $this->assert($officeMsgId > 0);
        } finally {
            if ($complaintId) {
                $pdo->exec("DELETE FROM complaint_messages WHERE complaint_id = {$complaintId}");
                $pdo->exec("DELETE FROM internal_notes WHERE complaint_id = {$complaintId}");
                $pdo->exec("DELETE FROM complaints WHERE id = {$complaintId}");
            }
            $pdo->exec("DELETE FROM office_messages WHERE citizen_user_id = {$citizenUserId}");
            $pdo->exec("DELETE FROM users WHERE id IN ({$citizenUserId}, {$officerUserId})");
        }
    }

    public function testPublicAccountabilityMetricsAndDirectory(): void
    {
        $metrics = $this->publicService->getPublicMetrics();
        $this->assertIsArray($metrics);
        $this->assertArrayHasKey('total_received', $metrics);
        $this->assertArrayHasKey('in_progress', $metrics);
        $this->assertArrayHasKey('work_completed', $metrics);
        $this->assertArrayHasKey('supervisor_verified', $metrics);
        $this->assertArrayHasKey('citizen_confirmed_resolved', $metrics);
        $this->assertArrayHasKey('currently_overdue', $metrics);

        $directory = $this->publicService->getWhoIsResponsible();
        $this->assertIsArray($directory);
    }
}
