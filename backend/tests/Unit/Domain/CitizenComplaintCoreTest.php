<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Unit\Domain;

use AmarMayor\Database\DatabaseManager;
use AmarMayor\Domain\ComplaintCore\ComplaintService;
use AmarMayor\Tests\TestCase;
use PDO;

class CitizenComplaintCoreTest extends TestCase
{
    private ComplaintService $complaintService;

    public function setUp(): void
    {
        parent::setUp();
        $this->complaintService = new ComplaintService();
    }

    public function testComplaintCreationAndPublicTrackingPrivacy(): void
    {
        $pdo = DatabaseManager::getConnection();

        // 1. Create a test citizen
        $stmt = $pdo->prepare("
            INSERT INTO users (uuid, phone, phone_lookup_hash, user_type, status, preferred_language, created_at)
            VALUES ('test-citizen-c1', '+8801711000001', 'hash-c1', 'citizen', 'active', 'bn', NOW())
        ");
        $stmt->execute();
        $citizenUserId = (int)$pdo->lastInsertId();

        $catId = (int)$pdo->query("SELECT id FROM complaint_categories WHERE slug = 'cleanliness'")->fetchColumn();
        $subId = (int)$pdo->query("SELECT id FROM complaint_subcategories WHERE slug = 'dustbin_overflow'")->fetchColumn();
        $wardId = (int)$pdo->query("SELECT id FROM wards WHERE ward_number = 1")->fetchColumn();

        $complaintId = 0;
        try {
            // 2. Submit complaint
            $complaint = $this->complaintService->createComplaint([
                'citizen_user_id' => $citizenUserId,
                'category_id' => $catId,
                'subcategory_id' => $subId,
                'ward_id' => $wardId,
                'description' => 'ডাস্টবিনে অতিরিক্ত বর্জ্য জমে দুর্গন্ধ ছড়াচ্ছে। দ্রুত অপসারণের অনুরোধ।',
                'latitude' => 24.7471234,
                'longitude' => 90.4203456,
                'approximate_address' => 'বাসা নং ১২, রোড ৩, নতুন বাজার, ময়মনসিংহ',
                'landmark' => 'নতুন বাজার জামে মসজিদের সামনে',
                'media' => [
                    [
                        'media_type' => 'image',
                        'file_path' => '/uploads/evidence/2026/08/dustbin_01.jpg',
                        'mime_type' => 'image/jpeg',
                        'file_size' => 204800,
                        'is_live' => true,
                    ]
                ]
            ]);

            $complaintId = (int)$complaint['id'];
            $this->assert($complaintId > 0);
            $this->assertStringContains('MCC-', $complaint['public_complaint_number']);
            $this->assertEquals('submitted', $complaint['status_history'][0]['to_internal_status']);
            $this->assertEquals('received', $complaint['status_history'][0]['to_citizen_status']);
            $this->assertCount(1, $complaint['media']);

            // 3. Test Privacy-Safe Public Tracking (unauthenticated or other user)
            $publicView = $this->complaintService->getComplaintByTrackingNumber($complaint['public_complaint_number'], null);
            $this->assertNotNull($publicView);
            $this->assertFalse(isset($publicView['citizen_user_id']), "Citizen ID must not be leaked to public");
            $this->assertFalse(isset($publicView['description']), "Raw citizen description must be protected in public view");
            $this->assertEquals('নতুন বাজার জামে মসজিদের সামনে', $publicView['public_safe_address']);
            $this->assertEquals(24.747, (float)$publicView['public_latitude']); // Rounded to 3 decimals

            // 4. Test Authenticated Citizen View (Citizen sees their own full data)
            $citizenView = $this->complaintService->getComplaintByTrackingNumber($complaint['public_complaint_number'], $citizenUserId);
            $this->assertNotNull($citizenView);
            $this->assertEquals($citizenUserId, (int)$citizenView['citizen_user_id']);
            $this->assertTrue(isset($citizenView['description']));
            $this->assertCount(1, $citizenView['media']);

            // 5. Test Citizen's My Complaints List
            $myList = $this->complaintService->getCitizenComplaints($citizenUserId);
            $this->assertCount(1, $myList);
            $this->assertEquals($complaint['public_complaint_number'], $myList[0]['public_complaint_number']);
        } finally {
            if ($complaintId) {
                $pdo->exec("DELETE FROM task_evidence WHERE media_id IN (SELECT id FROM complaint_media WHERE complaint_id = {$complaintId})");
                $pdo->exec("DELETE FROM complaint_media WHERE complaint_id = {$complaintId}");
                $pdo->exec("DELETE FROM complaint_locations WHERE complaint_id = {$complaintId}");
                $pdo->exec("DELETE FROM complaint_status_history WHERE complaint_id = {$complaintId}");
                $pdo->exec("DELETE FROM complaint_ownership_history WHERE complaint_id = {$complaintId}");
                $pdo->exec("DELETE FROM complaints WHERE id = {$complaintId}");
            }
            $pdo->exec("DELETE FROM users WHERE id = {$citizenUserId}");
        }
    }
}
