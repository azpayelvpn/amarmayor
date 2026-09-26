<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Feature;

use AmarMayor\Auth\Auth;
use AmarMayor\Auth\User;
use AmarMayor\Database\DatabaseManager;
use AmarMayor\Support\Security;
use AmarMayor\Tests\TestCase;
use PDO;

class RoleDashboardCoordinationTest extends TestCase
{
    public function testAll21SystemRolesCanRenderTheirUniqueDashboard(): void
    {
        $this->setUp();

        $roleAccounts = [
            'mayor' => ['email' => 'demo.mayor@demo.local', 'needle' => 'MAYOR'],
            'administrator' => ['email' => 'demo.administrator@demo.local', 'needle' => 'ADMINISTRATOR'],
            'ceo' => ['email' => 'demo.ceo@demo.local', 'needle' => 'CEO'],
            'general_councillor' => ['email' => 'demo.general_councillor@demo.local', 'needle' => 'COUNCILLOR'],
            'reserved_women_councillor' => ['email' => 'demo.reserved_women_councillor@demo.local', 'needle' => 'COUNCILLOR'],
            'responsible_officer' => ['email' => 'demo.responsible_officer@demo.local', 'needle' => 'RESPONSIBLE OFFICER'],
            'department_head' => ['email' => 'demo.department_head@demo.local', 'needle' => 'DEPARTMENT HEAD'],
            'department_officer' => ['email' => 'demo.department_officer@demo.local', 'needle' => 'DEPARTMENT OFFICER'],
            'zone_officer' => ['email' => 'demo.zone_officer@demo.local', 'needle' => 'ZONE OFFICER'],
            'ward_officer' => ['email' => 'demo.ward_officer@demo.local', 'needle' => 'WARD OFFICER'],
            'supervisor' => ['email' => 'demo.supervisor@demo.local', 'needle' => 'SUPERVISOR'],
            'team_leader' => ['email' => 'demo.team_leader@demo.local', 'needle' => 'TEAM LEADER'],
            'field_worker' => ['email' => 'demo.field_worker@demo.local', 'needle' => 'FIELD WORKER'],
            'call_center_operator' => ['email' => 'demo.call_center_operator@demo.local', 'needle' => 'CALL CENTER OPERATOR'],
            'control_room_officer' => ['email' => 'demo.control_room_officer@demo.local', 'needle' => 'CONTROL ROOM OFFICER'],
            'public_info_officer' => ['email' => 'demo.public_info_officer@demo.local', 'needle' => 'PUBLIC INFORMATION OFFICER'],
            'data_monitoring_officer' => ['email' => 'demo.data_monitoring_officer@demo.local', 'needle' => 'DATA & MONITORING OFFICER'],
            'auditor' => ['email' => 'demo.auditor@demo.local', 'needle' => 'AUDITOR'],
            'platform_super_admin' => ['email' => 'demo.platform_super_admin@demo.local', 'needle' => 'PLATFORM SUPER ADMIN'],
            'technical_super_admin' => ['email' => 'demo.technical_super_admin@demo.local', 'needle' => 'TECHNICAL SUPER ADMIN'],
            'public_viewer' => ['email' => 'demo.public_viewer@demo.local', 'needle' => 'PUBLIC VIEWER'],
        ];

        foreach ($roleAccounts as $roleKey => $info) {
            $user = User::findByEmail($info['email']);
            $this->assertNotNull($user, "User account for {$roleKey} ({$info['email']}) must exist");

            Auth::login($user);
            $res = $this->get('/dashboard');

            $this->assertEquals(200, $res->getStatusCode(), "Dashboard for role {$roleKey} must return HTTP 200");
            $content = $res->getContent();
            $this->assertStringContains($info['needle'], strtoupper($content), "Dashboard for role {$roleKey} must contain {$info['needle']}");
        }
    }

    public function testCeoCanSubmitExplanationRequest(): void
    {
        $this->setUp();
        $ceo = User::findByEmail('demo.ceo@demo.local');
        $this->assertNotNull($ceo);
        Auth::login($ceo);

        $pdo = DatabaseManager::getConnection();
        $complaintId = (int)$pdo->query("SELECT id FROM complaints LIMIT 1")->fetchColumn() ?: 1;
        $employeeId = (int)$pdo->query("SELECT id FROM employees LIMIT 1")->fetchColumn() ?: 1;

        $res = $this->post('/dashboard/ceo/explanation-requests', [
            '_csrf_token' => Security::generateCsrfToken(),
            'complaint_id' => $complaintId,
            'target_employee_id' => $employeeId,
            'reason' => 'বিলম্বের কারণ ব্যাখ্যা তলব — টেস্ট কেস',
            'deadline_hours' => 24,
        ]);

        $this->assertEquals(302, $res->getStatusCode());
        $this->assertStringContains('msg=explanation_requested', (string)$res->getHeader('Location'));

        $count = (int)$pdo->query("SELECT COUNT(*) FROM explanation_requests WHERE question LIKE '%টেস্ট কেস%'")->fetchColumn();
        $this->assertTrue($count > 0, 'Explanation request record must be saved in DB');
    }

    public function testCouncillorCanReferComplaint(): void
    {
        $this->setUp();
        $councillor = User::findByEmail('demo.general_councillor@demo.local');
        $this->assertNotNull($councillor);
        Auth::login($councillor);

        $pdo = DatabaseManager::getConnection();
        $catId = (int)$pdo->query("SELECT id FROM complaint_categories LIMIT 1")->fetchColumn() ?: 1;

        $res = $this->post('/dashboard/councillor/referral', [
            '_csrf_token' => Security::generateCsrfToken(),
            'ward_id' => 1,
            'category_id' => $catId,
            'description' => 'কাউন্সিলর সুপারিশকৃত জরুরি নর্দমা সংস্কার প্রয়োজন',
            'landmark' => 'ওয়ার্ড ১ বাজার মোড়',
        ]);

        $this->assertEquals(302, $res->getStatusCode());
        $this->assertStringContains('msg=referral_submitted', (string)$res->getHeader('Location'));

        $count = (int)$pdo->query("SELECT COUNT(*) FROM internal_notes WHERE note_type = 'councillor_note' AND note_text LIKE '%সুপারিশকৃত%'")->fetchColumn();
        $this->assertTrue($count > 0, 'Councillor referral internal note must be logged');
    }

    public function testResponsibleOfficerCanLogInspectionNote(): void
    {
        $this->setUp();
        $officer = User::findByEmail('demo.responsible_officer@demo.local');
        $this->assertNotNull($officer);
        Auth::login($officer);

        $pdo = DatabaseManager::getConnection();
        $complaintId = (int)$pdo->query("SELECT id FROM complaints LIMIT 1")->fetchColumn() ?: 1;

        $res = $this->post('/dashboard/inspection-notes', [
            '_csrf_token' => Security::generateCsrfToken(),
            'complaint_id' => $complaintId,
            'quality_rating' => 'good',
            'note_text' => 'সরেজমিনে কাজের মান সন্তোষজনক পাওয়া গেছে',
        ]);

        $this->assertEquals(302, $res->getStatusCode());
        $this->assertStringContains('msg=inspection_logged', (string)$res->getHeader('Location'));

        $noteCount = (int)$pdo->query("SELECT COUNT(*) FROM internal_notes WHERE note_type = 'inspection' AND note_text LIKE '%সরেজমিনে কাজের মান%'")->fetchColumn();
        $this->assertTrue($noteCount > 0, 'Inspection internal note must be logged');
    }

    public function testControlRoomOfficerCanReRouteComplaint(): void
    {
        $this->setUp();
        $officer = User::findByEmail('demo.control_room_officer@demo.local');
        $this->assertNotNull($officer);
        Auth::login($officer);

        $pdo = DatabaseManager::getConnection();
        $complaintId = (int)$pdo->query("SELECT id FROM complaints LIMIT 1")->fetchColumn() ?: 1;

        $res = $this->post('/dashboard/control-room/re-route', [
            '_csrf_token' => Security::generateCsrfToken(),
            'complaint_id' => $complaintId,
            'department_id' => 2,
            'ward_id' => 2,
            'reason' => 'কন্ট্রোল রুম থেকে টেস্ট রি-রাউটিং সম্পন্ন',
        ]);

        $this->assertEquals(302, $res->getStatusCode());
        $this->assertStringContains('msg=rerouted_successfully', (string)$res->getHeader('Location'));

        $noteCount = (int)$pdo->query("SELECT COUNT(*) FROM internal_notes WHERE note_type = 'reroute' AND note_text LIKE '%টেস্ট রি-রাউটিং%'")->fetchColumn();
        $this->assertTrue($noteCount > 0, 'Reroute internal note must be recorded');
    }

    public function testPublicInfoOfficerCanPublishNotice(): void
    {
        $this->setUp();
        $pio = User::findByEmail('demo.public_info_officer@demo.local');
        $this->assertNotNull($pio);
        Auth::login($pio);

        $res = $this->post('/dashboard/notices/create', [
            '_csrf_token' => Security::generateCsrfToken(),
            'notice_type' => 'general',
            'target_scope' => 'city',
            'title_bn' => 'ময়মনসিংহ শহরে বিশেষ মশক নিধন অভিযান সংক্রান্ত বিজ্ঞপ্তি',
            'title_en' => 'Citywide Mosquito Control Campaign Notice',
            'body_bn' => 'আগামীকাল থেকে সকল ওয়ার্ডে ফগিং ও লার্ভিসাইড কার্যক্রম একযোগে পরিচালিত হবে।',
            'body_en' => 'Fogging and larvicide operations will be conducted in all wards.',
        ]);

        $this->assertEquals(302, $res->getStatusCode());
        $this->assertStringContains('msg=notice_published', (string)$res->getHeader('Location'));

        $pdo = DatabaseManager::getConnection();
        $count = (int)$pdo->query("SELECT COUNT(*) FROM city_notices WHERE title_bn LIKE '%মশক নিধন%'")->fetchColumn();
        $this->assertTrue($count > 0, 'City notice must be inserted into DB');
    }

    public function testTeamLeaderCanReportRoadProgress(): void
    {
        $this->setUp();
        $leader = User::findByEmail('demo.team_leader@demo.local');
        $this->assertNotNull($leader);
        Auth::login($leader);

        $pdo = DatabaseManager::getConnection();
        $complaintId = (int)$pdo->query("SELECT id FROM complaints LIMIT 1")->fetchColumn() ?: 1;

        $res = $this->post('/dashboard/team-leader/report-progress', [
            '_csrf_token' => Security::generateCsrfToken(),
            'complaint_id' => $complaintId,
            'road_name' => 'টাউন হল রোড ও স্টেশন রোড সংযোগ',
            'progress_status' => 'completed',
            'notes' => 'সকাল ৯টায় পরিচ্ছন্নতা সম্পন্ন',
        ]);

        $this->assertEquals(302, $res->getStatusCode());
        $this->assertStringContains('msg=team_progress_reported', (string)$res->getHeader('Location'));

        $count = (int)$pdo->query("SELECT COUNT(*) FROM internal_notes WHERE note_type = 'team_progress' AND note_text LIKE '%টাউন হল রোড%'")->fetchColumn();
        $this->assertTrue($count > 0, 'Team progress note must be inserted into DB');
    }

    public function testTechnicalAdminCanRunJobsAndClearCache(): void
    {
        $this->setUp();
        $techAdmin = User::findByEmail('demo.technical_super_admin@demo.local');
        $this->assertNotNull($techAdmin);
        Auth::login($techAdmin);

        $res1 = $this->post('/dashboard/tech/run-jobs', [
            '_csrf_token' => Security::generateCsrfToken(),
        ]);
        $this->assertEquals(302, $res1->getStatusCode());
        $this->assertStringContains('msg=jobs_processed', (string)$res1->getHeader('Location'));

        $res2 = $this->post('/dashboard/tech/clear-cache', [
            '_csrf_token' => Security::generateCsrfToken(),
        ]);
        $this->assertEquals(302, $res2->getStatusCode());
        $this->assertStringContains('msg=cache_cleared', (string)$res2->getHeader('Location'));
    }
}
