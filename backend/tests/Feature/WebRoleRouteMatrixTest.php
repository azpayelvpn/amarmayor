<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Feature;

use AmarMayor\Auth\Auth;
use AmarMayor\Auth\User;
use AmarMayor\Database\DatabaseManager;
use AmarMayor\Support\Security;
use AmarMayor\Tests\TestCase;
use PDO;

class WebRoleRouteMatrixTest extends TestCase
{
    private PDO $pdo;

    public function setUp(): void
    {
        parent::setUp();
        $this->pdo = DatabaseManager::getConnection();
    }

    public function tearDown(): void
    {
        Auth::logout();
    }

    public function testUnauthenticatedWebRoutesMatrix(): void
    {
        $this->setUp();
        Auth::logout();

        $publicRoutes = [
            '/' => 200,
            '/htmx/status-check' => 200,
            '/complaints/create' => 200,
            '/submit' => 200,
            '/track' => 200,
            '/who-is-responsible' => 200,
            '/wards' => 200,
            '/notices' => 200,
            '/login' => 200,
            '/dev/testing-access' => 200,
            '/dev/otp-inbox' => 200,
            '/dashboard' => 302, // Redirects to login
            '/my-complaints' => 302, // Redirects to login
        ];

        foreach ($publicRoutes as $path => $expectedStatus) {
            $response = $this->get($path);
            $this->assertEquals(
                $expectedStatus,
                $response->getStatusCode(),
                "Route {$path} should return HTTP {$expectedStatus} for guest"
            );
            $this->assertTrue($response->getStatusCode() !== 500, "Route {$path} must never 500");
        }
    }

    public function testAuthRequiredMessageRendersCleanly(): void
    {
        $this->setUp();
        Auth::logout();

        // Bangla
        $respBn = $this->get('/login?error=auth_required');
        $this->assertEquals(200, $respBn->getStatusCode());
        $bodyBn = $respBn->getContent();
        $this->assertStringContains('এই পেইজটি দেখতে আগে লগইন করুন।', $bodyBn);
        $this->assertTrue(!str_contains($bodyBn, 'auth_required'));

        // English
        $respEn = $this->get('/login?error=auth_required&lang=en');
        $this->assertEquals(200, $respEn->getStatusCode());
        $bodyEn = $respEn->getContent();
        $this->assertStringContains('Please log in to view this page.', $bodyEn);
    }

    public function testTrackAndMyComplaintsDoNotCrashWithUserObject(): void
    {
        $this->setUp();
        
        // 1. Citizen login
        $citizen = User::findByEmail('demo.citizen@demo.local');
        $this->assertNotNull($citizen, 'Demo Citizen must exist');
        Auth::login($citizen);

        // Test GET /track with logged in citizen (exercises line 152 viewingUserId)
        $respTrack = $this->get('/track');
        $this->assertEquals(200, $respTrack->getStatusCode());
        $this->assertTrue($respTrack->getStatusCode() !== 500);

        // Test GET /my-complaints with logged in citizen (exercises line 223 getCitizenComplaints)
        $respMy = $this->get('/my-complaints');
        $this->assertEquals(200, $respMy->getStatusCode());
        $this->assertTrue($respMy->getStatusCode() !== 500);
        $this->assertStringContains('আমার অভিযোগ', $respMy->getContent());

        // 2. Staff user hitting /my-complaints (Must redirect safely to /dashboard without 500)
        Auth::logout();
        $mayor = User::findByEmail('demo.mayor@demo.local');
        $this->assertNotNull($mayor);
        Auth::login($mayor);

        $respStaffMy = $this->get('/my-complaints');
        $this->assertEquals(302, $respStaffMy->getStatusCode(), 'Staff should be safely redirected from /my-complaints');
        $this->assertEquals('/dashboard', (string)$respStaffMy->getHeader('Location'));
    }

    public function testAllCanonicalRolesWebMatrix(): void
    {
        $this->setUp();

        $demoUsers = $this->pdo->query("
            SELECT u.id, u.email, r.slug as role_slug
            FROM users u
            INNER JOIN user_roles ur ON ur.user_id = u.id
            INNER JOIN roles r ON r.id = ur.role_id
            WHERE u.email LIKE '%@demo.local'
            ORDER BY r.id ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $this->assertTrue(count($demoUsers) >= 20, 'At least 20 canonical demo users should exist');

        $routesToTest = [
            '/',
            '/track',
            '/wards',
            '/who-is-responsible',
            '/notices',
            '/dashboard',
            '/dev/testing-access',
            '/dev/otp-inbox',
        ];

        foreach ($demoUsers as $userRow) {
            Auth::logout();
            $user = User::findByEmail($userRow['email']);
            $this->assertNotNull($user, "User object must exist for {$userRow['email']}");
            Auth::login($user);

            foreach ($routesToTest as $path) {
                $resp = $this->get($path);
                $status = $resp->getStatusCode();

                $this->assertTrue(
                    in_array($status, [200, 302], true),
                    "Role {$userRow['role_slug']} ({$userRow['email']}) visiting {$path} returned unexpected status {$status}"
                );
                $this->assertTrue($status !== 500, "Role {$userRow['role_slug']} visiting {$path} resulted in 500 error!");
            }
        }
    }

    public function testRoleBoundaryEnforcementOnTaskActions(): void
    {
        $this->setUp();

        // 1. General Councillor attempts to execute field task -> 403 Forbidden
        $councillor = User::findByEmail('demo.general_councillor@demo.local');
        $this->assertNotNull($councillor);
        Auth::login($councillor);

        $respCouncillorStart = $this->post('/dashboard/tasks/1/start', [
            '_csrf_token' => Security::generateCsrfToken(),
        ]);
        $this->assertEquals(403, $respCouncillorStart->getStatusCode());
        $this->assertStringContains('অনুমতি নেই', $respCouncillorStart->getContent());

        // 2. Councillor attempts supervisor verify -> 403 Forbidden
        $respCouncillorVerify = $this->post('/dashboard/tasks/1/verify', [
            '_csrf_token' => Security::generateCsrfToken(),
        ]);
        $this->assertEquals(403, $respCouncillorVerify->getStatusCode());

        // 3. Supervisor attempts supervisor verify on valid task -> 302 with success msg
        Auth::logout();
        $supervisor = User::findByEmail('demo.supervisor@demo.local');
        $this->assertNotNull($supervisor);
        Auth::login($supervisor);

        // Get an active task
        $taskId = (int)$this->pdo->query("SELECT id FROM field_tasks LIMIT 1")->fetchColumn();
        if ($taskId > 0) {
            $respSupVerify = $this->post("/dashboard/tasks/{$taskId}/verify", [
                '_csrf_token' => Security::generateCsrfToken(),
            ]);
            $this->assertEquals(302, $respSupVerify->getStatusCode());
            $this->assertStringContains('/dashboard?msg=task_verified', (string)$respSupVerify->getHeader('Location'));
        }
    }
}
