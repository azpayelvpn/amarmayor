<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Feature;

use AmarMayor\Auth\Auth;
use AmarMayor\Auth\AuthService;
use AmarMayor\Auth\User;
use AmarMayor\Controllers\Web\ComplaintWebController;
use AmarMayor\Controllers\Web\DashboardWebController;
use AmarMayor\Controllers\Web\ProfileWebController;
use AmarMayor\Database\DatabaseManager;
use AmarMayor\Database\Seeders\RealisticDemoSeeder;
use AmarMayor\Database\Seeders\StructuralSeeder;
use AmarMayor\Database\Seeders\VerifiedMccDataSeeder;
use AmarMayor\Http\Request;
use AmarMayor\Support\Config;
use AmarMayor\Tests\TestCase;
use PDO;

class ProductIntegrityAndDemoDataTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        StructuralSeeder::run();
        RealisticDemoSeeder::run();
    }

    public function testCitizenOtpLoginResolvesStrictlyToCitizen(): void
    {
        $authService = new AuthService();
        $phone = '01711' . random_int(100000, 999999);

        // 1. Request OTP
        $challenge = $authService->requestOtp($phone);
        $this->assertNotEmpty($challenge['mock_otp'] ?? '');

        // 2. Verify OTP
        $user = $authService->verifyOtp($phone, $challenge['mock_otp']);
        $this->assertNotNull($user);
        $this->assertEquals('citizen', $user->userType);
        $this->assertTrue($user->hasRole('citizen'));
        $this->assertFalse($user->hasRole('public_viewer'));

        // 3. Visiting /dashboard as Citizen redirects to /my-complaints
        Auth::login($user);
        $dashboardController = new DashboardWebController();
        $response = $dashboardController->index(new Request('GET', '/dashboard'));
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/my-complaints', $response->getHeader('location'));
        Auth::logout();
    }

    public function testCitizenProfileGetAndUpdate(): void
    {
        $pdo = DatabaseManager::getConnection();
        $user = User::findByPhone('01711000001');
        $this->assertNotNull($user);

        Auth::login($user);
        $controller = new ProfileWebController();

        // 1. Show profile
        $resp = $controller->show(new Request('GET', '/profile'));
        $this->assertEquals(200, $resp->getStatusCode());
        $content = $resp->getContent();
        $this->assertStringContainsString('আমার প্রোফাইল ও অ্যাকাউন্ট', $content);
        $this->assertStringContainsString($user->phone, $content);

        // 2. Update profile
        $updateReq = new Request('POST', '/profile', [], [
            'name_bn' => 'রফিকুল ইসলাম (আপডেটেড)',
            'name_en' => 'Rafiqul Islam Updated',
            'email' => 'rafiq.updated@demo.local',
            'home_ward_id' => '1',
            'home_area' => 'গাঙ্গিনার পাড় মোড়',
            'preferred_language' => 'bn',
            'notif_sms' => '1',
            'notif_app' => '1',
        ]);
        $updateResp = $controller->update($updateReq);
        $this->assertEquals(302, $updateResp->getStatusCode());
        $this->assertStringContainsString('/profile?success=profile_updated', $updateResp->getHeader('location'));

        // Verify in DB
        $p = $pdo->query("SELECT * FROM persons WHERE user_id = {$user->id}")->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals('রফিকুল ইসলাম (আপডেটেড)', $p['full_name_bn']);
        $this->assertEquals('Rafiqul Islam Updated', $p['full_name_en']);
        $this->assertEquals(1, (int)$p['home_ward_id']);
        $this->assertEquals('গাঙ্গিনার পাড় মোড়', $p['home_area']);

        // Restore clean baseline for subsequent tests
        $pdo->exec("UPDATE users SET email = 'demo.citizen@demo.local' WHERE id = {$user->id}");

        Auth::logout();
    }

    public function testPublicTrackingPrivacyAndCitizenConfirmationGate(): void
    {
        $pdo = DatabaseManager::getConnection();
        $citizenA = User::findByPhone('01711000001');
        $citizenB = User::findByPhone('01711000002');
        $this->assertNotNull($citizenA);
        $this->assertNotNull($citizenB);

        $complaintController = new ComplaintWebController();

        // 1. View tracking MCC-DEMO-002 as Unauthenticated Public Viewer
        Auth::logout();
        $pubResp = $complaintController->track(new Request('GET', '/track/MCC-DEMO-002'), 'MCC-DEMO-002');
        $this->assertEquals(200, $pubResp->getStatusCode());
        $pubContent = $pubResp->getContent();

        // Must show safe tracking and timeline
        $this->assertStringContainsString('MCC-DEMO-002', $pubContent);
        // Must NEVER expose private phone or email
        $this->assertStringNotContainsString('01711000001', $pubContent);
        $this->assertStringNotContainsString('01711000002', $pubContent);
        $this->assertStringNotContainsString('demo.citizen@demo.local', $pubContent);
        // Must NOT show confirmation form to unauthenticated public viewer
        $this->assertStringNotContainsString('id="confirmBox"', $pubContent);

        // 2. View tracking as Citizen Owner
        $c2OwnerId = (int)$pdo->query("SELECT citizen_user_id FROM complaints WHERE public_complaint_number = 'MCC-DEMO-002'")->fetchColumn();
        $c2Owner = User::findById($c2OwnerId);
        $this->assertNotNull($c2Owner);
        Auth::login($c2Owner);
        $ownerResp = $complaintController->track(new Request('GET', '/track/MCC-DEMO-002'), 'MCC-DEMO-002');
        $this->assertEquals(200, $ownerResp->getStatusCode());
        $ownerContent = $ownerResp->getContent();
        // Citizen Owner MUST see confirmation form
        $this->assertStringContainsString('id="confirmBox"', $ownerContent);
        $this->assertStringContainsString('সমস্যা সমাধান হয়েছে', $ownerContent);

        // 3. Non-owner cannot execute confirmation
        $nonOwner = ($c2OwnerId === $citizenA->id) ? $citizenB : $citizenA;
        Auth::login($nonOwner);
        $c2Id = (int)$pdo->query("SELECT id FROM complaints WHERE public_complaint_number = 'MCC-DEMO-002'")->fetchColumn();
        $unauthPost = new Request('POST', "/complaints/{$c2Id}/confirm-resolution", [], ['action' => 'confirm', 'rating' => '5']);
        $unauthResp = $complaintController->confirmResolution($unauthPost, (string)$c2Id);
        $this->assertEquals(302, $unauthResp->getStatusCode());
        $this->assertStringContainsString('error=', $unauthResp->getHeader('location'));

        Auth::logout();
    }

    public function testVerifiedMccOfficersHaveNoLoginCredentials(): void
    {
        $pdo = DatabaseManager::getConnection();
        VerifiedMccDataSeeder::run();

        // 1. All verified official persons must have user_id = NULL
        $officers = $pdo->query("SELECT * FROM persons WHERE is_demo = 0 AND source_name = 'MCC Official Administration Directory & LGRD Gazette'")->fetchAll(PDO::FETCH_ASSOC);
        $this->assertGreaterThanOrEqual(10, count($officers));

        foreach ($officers as $o) {
            $this->assertNull($o['user_id'], "Verified official {$o['full_name_en']} must NOT have a linked user account!");
            $this->assertEquals('verified_current', $o['verification_status']);
            $this->assertEquals(0, (int)$o['is_demo']);
            $this->assertNotEmpty($o['source_name']);
            $this->assertNotEmpty($o['source_url']);
        }

        // 2. No password login possible for real official emails
        $authService = new AuthService();
        $failLogin = $authService->authenticateWithPassword('administrator@mcc.gov.bd', 'Demo@12345');
        $this->assertNull($failLogin, "Real official email must NEVER be able to log in with demo password!");
    }

    public function testCitizenAOwnsMultipleFlagshipComplaintsWithDifferentStatuses(): void
    {
        $pdo = DatabaseManager::getConnection();
        $citizenA = User::findByPhone('01711000001');
        $citizenB = User::findByPhone('01711000002');
        $this->assertNotNull($citizenA);
        $this->assertNotNull($citizenB);

        $complaintService = new \AmarMayor\Domain\ComplaintCore\ComplaintService();
        $aComplaints = $complaintService->getCitizenComplaints($citizenA->id);
        $bComplaints = $complaintService->getCitizenComplaints($citizenB->id);

        $this->assertGreaterThanOrEqual(5, count($aComplaints), "Citizen A must have multiple owned complaints!");
        $this->assertGreaterThanOrEqual(2, count($bComplaints), "Citizen B must have owned complaints!");

        // Verify status diversity for Citizen A
        $statuses = array_column($aComplaints, 'citizen_status');
        $this->assertContains('received', $statuses);
        $this->assertContains('in_progress', $statuses);
        $this->assertContains('confirmation_needed', $statuses);
        $this->assertContains('needs_more_work', $statuses);
        $this->assertContains('resolved', $statuses);

        // Verify strict isolation (no complaint overlap)
        $aTracking = array_column($aComplaints, 'public_complaint_number');
        $bTracking = array_column($bComplaints, 'public_complaint_number');
        $overlap = array_intersect($aTracking, $bTracking);
        $this->assertEmpty($overlap, "Citizen A and Citizen B must NOT have overlapping complaints!");

        // Verify /my-complaints renders correctly for Citizen A
        Auth::login($citizenA);
        $controller = new ComplaintWebController();
        $resp = $controller->myComplaints(new Request('GET', '/my-complaints'));
        $this->assertEquals(200, $resp->getStatusCode());
        $content = $resp->getContent();
        $this->assertStringContainsString('MCC-DEMO-001', $content);
        $this->assertStringContainsString('MCC-DEMO-002', $content);
        $this->assertStringContainsString('MCC-DEMO-003', $content);
        $this->assertStringContainsString('MCC-DEMO-004', $content);
        $this->assertStringContainsString('MCC-DEMO-005', $content);
        $this->assertStringContainsString('MCC-DEMO-006', $content);
        Auth::logout();
    }

    public function testPhoneVerifiedBadgeRenderedInProfile(): void
    {
        $citizen = User::findByPhone('01711000001');
        $this->assertNotNull($citizen);
        Auth::login($citizen);

        $controller = new ProfileWebController();
        $resp = $controller->show(new Request('GET', '/profile'));
        $content = $resp->getContent();

        $this->assertStringContainsString('মোবাইল নম্বর যাচাইকৃত', $content);
        $this->assertStringNotContainsString('যাচাইকৃত অ্যাকাউন্ট', $content);
        $this->assertStringNotContainsString('Verified Account', $content);
        Auth::logout();
    }

    public function testPersonalizedMyAreaForCitizenWithHomeWard(): void
    {
        $citizen = User::findByPhone('01711000001');
        $this->assertNotNull($citizen);
        Auth::login($citizen);

        $controller = new \AmarMayor\Controllers\Web\CivicDirectoryWebController();
        $resp = $controller->wards(new Request('GET', '/my-area'));
        $this->assertEquals(200, $resp->getStatusCode());
        $content = $resp->getContent();

        $this->assertStringContainsString('আমার নির্ধারিত ওয়ার্ড', $content);
        $this->assertStringContainsString('ওয়ার্ড নং ১', $content);
        $this->assertStringContainsString('প্রশাসনিক ও সেবা দায়িত্ব', $content);
        $this->assertStringContainsString('+8809166666', $content);
        $this->assertStringContainsString('সাধারণ ওয়ার্ড', $content);
        Auth::logout();
    }

    public function testWhoIsResponsibleEmptyStateAndServiceResponsibility(): void
    {
        $controller = new \AmarMayor\Controllers\Web\CivicDirectoryWebController();
        $resp = $controller->whoIsResponsible(new Request('GET', '/who-is-responsible'));
        $this->assertEquals(200, $resp->getStatusCode());
        $content = $resp->getContent();

        $this->assertStringContainsString('এই ওয়ার্ডের যাচাইকৃত প্রতিনিধিত্ব/দায়িত্বপ্রাপ্ত তথ্য এখনো যোগ হয়নি।', $content);
        $this->assertStringContainsString('সেবা ও প্রশাসনিক দায়িত্ব:', $content);
        $this->assertStringContainsString('+8809166666', $content);
        $this->assertStringContainsString('id="noWardsFound"', $content);
    }

    public function testMayorDashboardKpiMetricsRealism(): void
    {
        $commandCenter = new \AmarMayor\Domain\CommandCenter\CommandCenterService();
        $kpis = $commandCenter->getExecutiveKpis();

        $total = $kpis['total_complaints'];
        $overdue = $kpis['overdue_count'];
        $satisfaction = $kpis['citizen_satisfaction_percent'];
        $feedbackCount = $kpis['total_feedback'];

        $this->assertGreaterThan(500, $total);
        // Overdue must be realistic (under 10% of total complaints)
        $this->assertLessThan($total * 0.10, $overdue);
        $this->assertGreaterThan(10, $overdue);

        // Citizen satisfaction must be between 70% and 95%
        $this->assertNotNull($satisfaction);
        $this->assertGreaterThanOrEqual(70.0, $satisfaction);
        $this->assertLessThanOrEqual(95.0, $satisfaction);
        $this->assertGreaterThan(50, $feedbackCount);

        // Unique attention queue items
        $attentionService = new \AmarMayor\Domain\ExecutiveAttention\ExecutiveAttentionService();
        $queue = $attentionService->getAttentionQueue();
        $this->assertNotEmpty($queue);
        $this->assertLessThan(100, count($queue));
    }
}
