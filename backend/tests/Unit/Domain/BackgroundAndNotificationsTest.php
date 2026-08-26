<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Unit\Domain;

use AmarMayor\Database\DatabaseManager;
use AmarMayor\Domain\Background\BackgroundJobService;
use AmarMayor\Domain\Notifications\NotificationService;
use AmarMayor\Support\Security;
use AmarMayor\Tests\TestCase;
use PDO;

class BackgroundAndNotificationsTest extends TestCase
{
    private BackgroundJobService $jobService;
    private NotificationService $notificationService;

    public function setUp(): void
    {
        parent::setUp();
        $this->jobService = new BackgroundJobService();
        $this->notificationService = new NotificationService();
    }

    public function testBackgroundJobDispatchAndProcessing(): void
    {
        $pdo = DatabaseManager::getConnection();

        // 1. Dispatch a job
        $jobId = $this->jobService->dispatch('scan_overdue_deadlines', ['triggered_by' => 'test_runner']);
        $this->assert($jobId > 0);

        // 2. Process pending jobs
        $processed = $this->jobService->processPendingJobs(5);
        $this->assert($processed >= 1);

        // 3. Verify job completed
        $job = $pdo->query("SELECT status FROM background_jobs WHERE id = {$jobId}")->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals('completed', $job['status']);

        // 4. Run Scheduled Maintenance
        $mResult = $this->jobService->runScheduledMaintenance();
        $this->assertIsArray($mResult);
        $this->assertArrayHasKey('overdue_breaches_detected', $mResult);
    }

    public function testNotificationQueuingAndRetrieval(): void
    {
        $pdo = DatabaseManager::getConnection();

        $stmt = $pdo->prepare("INSERT INTO users (uuid, phone, user_type, status, preferred_language, created_at) VALUES (?, '+8801744000001', 'citizen', 'active', 'bn', NOW())");
        $stmt->execute([Security::uuid()]);
        $userId = (int)$pdo->lastInsertId();

        $notifId = 0;
        try {
            $notifId = $this->notificationService->notifyUser(
                $userId,
                'অভিযোগ হালনাগাদ',
                'Complaint Update',
                'আপনার অভিযোগটির কাজ শুরু হয়েছে।',
                'Work on your complaint has commenced.',
                'sms'
            );
            $this->assert($notifId > 0);

            $notifications = $this->notificationService->getUserNotifications($userId);
            $this->assertCount(1, $notifications);
            $this->assertEquals('অভিযোগ হালনাগাদ', $notifications[0]['title_bn']);
        } finally {
            if ($notifId) {
                $pdo->exec("DELETE FROM notifications WHERE id = {$notifId}");
            }
            $pdo->exec("DELETE FROM users WHERE id = {$userId}");
        }
    }
}
