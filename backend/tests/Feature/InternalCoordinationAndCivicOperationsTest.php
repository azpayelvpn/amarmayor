<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Feature;

use AmarMayor\Auth\Auth;
use AmarMayor\Auth\User;
use AmarMayor\Database\DatabaseManager;
use AmarMayor\Domain\ComplaintCore\ComplaintService;
use AmarMayor\Domain\ExecutiveAttention\ExecutiveAttentionService;
use AmarMayor\Domain\FieldOperations\FieldTaskService;
use AmarMayor\Domain\Notifications\NotificationService;
use AmarMayor\Support\Security;
use AmarMayor\Tests\TestCase;
use PDO;

class InternalCoordinationAndCivicOperationsTest extends TestCase
{
    private PDO $pdo;
    private int $citizenUserId;

    public function setUp(): void
    {
        parent::setUp();
        $this->pdo = DatabaseManager::getConnection();

        $citId = $this->pdo->query("SELECT id FROM users WHERE user_type = 'citizen' ORDER BY id ASC LIMIT 1")->fetchColumn();
        if (!$citId) {
            $uuid = Security::uuid();
            $this->pdo->exec("INSERT INTO users (uuid, user_type, phone, status, preferred_language, created_at) VALUES ('{$uuid}', 'citizen', '01711000001', 'active', 'bn', NOW())");
            $this->citizenUserId = (int)$this->pdo->lastInsertId();
        } else {
            $this->citizenUserId = (int)$citId;
        }
    }

    public function testNotificationCenterAndNavbarIntegration(): void
    {
        $this->setUp();

        $user = User::findByEmail('demo.mayor@demo.local') ?: User::findById($this->citizenUserId);
        Auth::setUser($user);

        $notifService = new NotificationService();
        $notifId = $notifService->notifyUser(
            $user->id,
            'জরুরি সমন্বয় বিজ্ঞপ্তি',
            'Emergency Coordination Alert',
            '১ নং ওয়ার্ডে ড্রেন সংস্কার কাজে প্রকৌশল বিভাগের বুলডোজার প্রেরণ করা হয়েছে।',
            'Bulldozer dispatched to Ward 1.',
            'cross_department_support'
        );
        $this->assert($notifId > 0);

        // 2. Access notification center page
        $res = $this->get('/notifications');
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertStringContains('অভ্যন্তরীণ নোটিফিকেশন ও অ্যালার্ট কেন্দ্র', $res->getContent());
        $this->assertStringContains('জরুরি সমন্বয় বিজ্ঞপ্তি', $res->getContent());

        // 3. Check unread count
        $countRes = $this->get('/notifications/unread-count');
        $this->assertEquals(200, $countRes->getStatusCode());
        $this->assertStringContains('"unread_count":', $countRes->getContent());

        // 4. Mark notification as read
        $markRes = $this->post("/notifications/{$notifId}/read", ['_csrf_token' => Security::generateCsrfToken()]);
        $this->assertEquals(302, $markRes->getStatusCode());

        // 5. Mark all as read
        $allRes = $this->post('/notifications/read-all', ['_csrf_token' => Security::generateCsrfToken()]);
        $this->assertEquals(302, $allRes->getStatusCode());

        $this->assertEquals(0, $notifService->getUnreadCount($user->id));
    }

    public function testCommunityUpvoteAndSupportersCounter(): void
    {
        $this->setUp();

        // Create a test complaint
        $complaintService = new ComplaintService();
        $complaint = $complaintService->createComplaint([
            'citizen_user_id' => $this->citizenUserId,
            'category_id' => 1,
            'subcategory_id' => 1,
            'ward_id' => 1,
            'description' => 'রাস্তায় ময়লার স্তূপ পড়ে আছে, পথচারীদের অসুবিধা হচ্ছে।',
            'landmark' => 'জিলা স্কুল মোড়',
        ]);
        $complaintId = (int)$complaint['id'];
        $trackingNum = $complaint['public_complaint_number'];

        // Track complaint
        $trackRes = $this->get("/track/{$trackingNum}");
        $this->assertEquals(200, $trackRes->getStatusCode());
        $this->assertStringContains('আমিও ভুক্তভোগী', $trackRes->getContent());

        // Citizen supports complaint
        $supporter = User::findById($this->citizenUserId);
        Auth::setUser($supporter);

        $postRes = $this->post("/complaints/{$complaintId}/support", ['_csrf_token' => Security::generateCsrfToken()]);
        $this->assertEquals(302, $postRes->getStatusCode());

        // Verify count in database
        $cnt = (int)$this->pdo->query("SELECT COUNT(*) FROM complaint_supporters WHERE complaint_id = {$complaintId}")->fetchColumn();
        $this->assertEquals(1, $cnt);

        // Reload track page
        $reloadRes = $this->get("/track/{$trackingNum}");
        $this->assertEquals(200, $reloadRes->getStatusCode());
        $this->assertStringContains('১ জন', $reloadRes->getContent());
        $this->assertStringContains('সমর্থিত', $reloadRes->getContent());
    }

    public function testEmergencyCivicHazardCreationAndNotification(): void
    {
        $this->setUp();

        // Submit emergency complaint via web
        $postRes = $this->post('/complaints/create', [
            '_csrf_token' => Security::generateCsrfToken(),
            'category_id' => 1,
            'subcategory_id' => 1,
            'ward_id' => 2,
            'description' => 'খোলা ম্যানহোল প্রধান সড়কে, যে কোনো সময় মারাত্মক দুর্ঘটনা ঘটতে পারে!',
            'landmark' => 'রেলওয়ে স্টেশন রোড',
            'phone' => '01799887766',
            'is_emergency' => '1',
        ]);

        $this->assertEquals(200, $postRes->getStatusCode());
        preg_match('/MCC-\d{4}-\d{5}/', $postRes->getContent(), $matches);
        $this->assert(!empty($matches[0]));
        $trackingNumber = $matches[0];

        // Verify complaint priority in DB
        $stmt = $this->pdo->prepare("SELECT priority, operational_classification FROM complaints WHERE public_complaint_number = ?");
        $stmt->execute([$trackingNumber]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals('p1_urgent', $row['priority']);
        $this->assertEquals('emergency', $row['operational_classification']);

        // Verify emergency notifications queued
        $nStmt = $this->pdo->prepare("SELECT COUNT(*) FROM notifications WHERE notification_type = 'emergency_alert'");
        $nStmt->execute();
        $this->assert((int)$nStmt->fetchColumn() > 0);
    }

    public function testBeforeAndAfterPhotoResolutionProof(): void
    {
        $this->setUp();

        $complaintService = new ComplaintService();
        $complaint = $complaintService->createComplaint([
            'citizen_user_id' => $this->citizenUserId,
            'category_id' => 1,
            'subcategory_id' => 1,
            'ward_id' => 1,
            'description' => 'ড্রেনের ময়লা উপচে পড়ছে।',
            'media' => [
                [
                    'media_type' => 'image',
                    'file_path' => '/uploads/complaints/test_before_photo.jpg',
                    'mime_type' => 'image/jpeg',
                    'file_size' => 102400,
                ],
            ],
        ]);
        $complaintId = (int)$complaint['id'];
        $trackingNumber = $complaint['public_complaint_number'];

        // Verify before media attached
        $bCount = (int)$this->pdo->query("SELECT COUNT(*) FROM complaint_media WHERE complaint_id = {$complaintId}")->fetchColumn();
        $this->assertEquals(1, $bCount);

        // Create task and add resolution evidence
        $fieldTaskService = new FieldTaskService();
        $supEmpId = (int)$this->pdo->query("SELECT id FROM employees ORDER BY id ASC LIMIT 1")->fetchColumn() ?: 1;
        $taskId = $fieldTaskService->createTask($complaintId, $supEmpId, $supEmpId);
        $evidenceMediaId = $fieldTaskService->submitEvidence($taskId, $this->citizenUserId, '/uploads/complaints/test_after_photo.jpg', 'after_work', 'image');
        $this->assert($evidenceMediaId > 0);

        // Verify task evidence table has after_work
        $teCount = (int)$this->pdo->query("SELECT COUNT(*) FROM task_evidence WHERE field_task_id = {$taskId} AND evidence_stage = 'after_work'")->fetchColumn();
        $this->assertEquals(1, $teCount);

        // Load public tracking page
        $trackRes = $this->get("/track/{$trackingNumber}");
        $this->assertEquals(200, $trackRes->getStatusCode());
        $this->assertStringContains('কাজের পূর্বের ছবি (সমস্যা)', $trackRes->getContent());
        $this->assertStringContains('কাজের পরের ছবি (সমাধান)', $trackRes->getContent());
        $this->assertStringContains('test_before_photo.jpg', $trackRes->getContent());
        $this->assertStringContains('test_after_photo.jpg', $trackRes->getContent());
    }

    public function testExecutiveDirectiveIssuanceAndSupervisorReply(): void
    {
        $this->setUp();

        $complaintService = new ComplaintService();
        $complaint = $complaintService->createComplaint([
            'citizen_user_id' => $this->citizenUserId,
            'category_id' => 1,
            'subcategory_id' => 1,
            'ward_id' => 1,
            'description' => 'গুরুত্বপূর্ণ সড়কে ড্রেনের ঢাকনা ভাঙা।',
        ]);
        $complaintId = (int)$complaint['id'];

        // Mayor issues directive
        $mayor = User::findByEmail('demo.mayor@demo.local');
        $execService = new ExecutiveAttentionService();
        $directiveId = $execService->issueDirective($mayor ? $mayor->id : 1, $complaintId, 'expedite', 'আজকের মধ্যেই কাজ শেষ করে ফটো রিপোর্ট দিন।');
        $this->assert($directiveId > 0);

        // Supervisor responds to directive
        $supUser = User::findByEmail('demo.supervisor@demo.local') ?: User::findByEmail('supervisor.ward1@demo.local');
        if ($supUser) {
            Auth::setUser($supUser);
        }

        $replyRes = $this->post("/dashboard/directives/{$directiveId}/respond", [
            '_csrf_token' => Security::generateCsrfToken(),
            'response_text' => 'কাজ শুরু হয়েছে, দ্রুততম সময়ে সমাপ্তির ছবি জমা দেওয়া হবে।',
        ]);
        $this->assertEquals(302, $replyRes->getStatusCode());

        // Verify directive status in DB
        $dStmt = $this->pdo->prepare("SELECT status, response_text FROM executive_directives WHERE id = ?");
        $dStmt->execute([$directiveId]);
        $directive = $dStmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals('acknowledged', $directive['status']);
        $this->assertStringContains('কাজ শুরু হয়েছে', $directive['response_text']);

        // Verify internal notes logged
        $nStmt = $this->pdo->prepare("SELECT COUNT(*) FROM internal_notes WHERE complaint_id = ? AND note_type = 'directive_reply'");
        $nStmt->execute([$complaintId]);
        $this->assert((int)$nStmt->fetchColumn() > 0);
    }

    public function testCrossDepartmentSupportRequestsWorkflow(): void
    {
        $this->setUp();

        $complaintService = new ComplaintService();
        $complaint = $complaintService->createComplaint([
            'citizen_user_id' => $this->citizenUserId,
            'category_id' => 1,
            'subcategory_id' => 1,
            'ward_id' => 1,
            'description' => 'ড্রেনে বড় স্ল্যাব ভেঙে গেছে, ভারী ক্রেন প্রয়োজন।',
        ]);
        $complaintId = (int)$complaint['id'];

        $supUser = User::findByEmail('demo.supervisor@demo.local') ?: User::findByEmail('supervisor.ward1@demo.local');
        if ($supUser) {
            Auth::setUser($supUser);
        }

        // Supervisor submits support request to Dept 1
        $postRes = $this->post('/dashboard/support-requests/create', [
            '_csrf_token' => Security::generateCsrfToken(),
            'complaint_id' => $complaintId,
            'support_type' => 'machinery',
            'target_department_id' => 1,
            'details' => 'ম্যানহোলের স্ল্যাব ওঠাতে হাইড্রোলিক ক্রেন ও টিম সহায়তা প্রয়োজন।',
        ]);
        $this->assertEquals(302, $postRes->getStatusCode());

        // Verify support request created
        $srRow = $this->pdo->query("SELECT * FROM support_requests WHERE complaint_id = {$complaintId} ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        $this->assertNotNull($srRow);
        $this->assertEquals('pending', $srRow['status']);
        $this->assertEquals('machinery', $srRow['support_type']);

        // Department Head responds
        $headUser = User::findByEmail('demo.department_head@demo.local');
        if ($headUser) {
            Auth::setUser($headUser);
        }

        $respRes = $this->post("/dashboard/support-requests/{$srRow['id']}/respond", [
            '_csrf_token' => Security::generateCsrfToken(),
            'status' => 'approved',
            'response_notes' => '১টি হাইড্রোলিক ক্রেন ও ৩ জন চালক বরাদ্দ দেওয়া হলো।',
        ]);
        $this->assertEquals(302, $respRes->getStatusCode());

        // Verify approved status in DB
        $updatedSr = $this->pdo->query("SELECT status, response_notes FROM support_requests WHERE id = {$srRow['id']}")->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals('approved', $updatedSr['status']);
        $this->assertStringContains('হাইড্রোলিক ক্রেন', $updatedSr['response_notes']);
    }

    public function testCallCenterRapidPhoneIntakeSubmission(): void
    {
        $this->setUp();

        $operatorUser = User::findByEmail('demo.call_center@demo.local');
        if (!$operatorUser) {
            $operatorUser = User::findByEmail('demo.platform_super_admin@demo.local') ?: User::findById($this->citizenUserId);
        }
        Auth::setUser($operatorUser);

        $postRes = $this->post('/dashboard/call-center/submit', [
            '_csrf_token' => Security::generateCsrfToken(),
            'phone' => '01812345678',
            'ward_id' => 3,
            'category_id' => 1,
            'subcategory_id' => 1,
            'landmark' => 'গাঙ্গিনার পাড় মোড়',
            'description' => 'ফোন কল ইনটেক: প্রধান সড়কে বর্জ্যের দুর্গন্ধ, দ্রুত অপসারণ প্রয়োজন।',
            'is_emergency' => '0',
        ]);

        $this->assertEquals(302, $postRes->getStatusCode());

        // Verify complaint created with created_by_user_id as operator
        $cRow = $this->pdo->query("SELECT * FROM complaints WHERE created_by_user_id = {$operatorUser->id} ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        $this->assertNotNull($cRow);
        $this->assertEquals(3, (int)$cRow['ward_id']);
        $this->assertStringContains('গাঙ্গিনার পাড়', $this->pdo->query("SELECT landmark FROM complaint_locations WHERE complaint_id = {$cRow['id']}")->fetchColumn());

        // Verify SMS simulation
        $nStmt = $this->pdo->prepare("SELECT COUNT(*) FROM notifications WHERE notification_type = 'sms' AND user_id = ?");
        $nStmt->execute([(int)$cRow['citizen_user_id']]);
        $this->assert((int)$nStmt->fetchColumn() > 0);
    }

    public function testCivicSchedulesAndCharterLoads(): void
    {
        $this->setUp();

        $res = $this->get('/schedules');
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertStringContains('নাগরিক সেবা সনদ ও নিয়মিত সময়সূচি', $res->getContent());
        $this->assertStringContains('বর্জ্য অপসারণ সময়সূচি', $res->getContent());
        $this->assertStringContains('মশক নিধন রুটিন', $res->getContent());
        $this->assertStringContains('সকাল ০৬:০০ — সকাল ১০:০০', $res->getContent());
        $this->assertStringContains('২ হতে ৪ ঘণ্টা', $res->getContent());
    }
}
