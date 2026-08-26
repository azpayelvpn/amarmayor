<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Feature;

use AmarMayor\Auth\Auth;
use AmarMayor\Auth\User;
use AmarMayor\Database\DatabaseManager;
use AmarMayor\Support\Security;
use AmarMayor\Tests\TestCase;

class RoleDashboardWebTest extends TestCase
{
    public function testUnauthenticatedDashboardRedirectsToLogin(): void
    {
        $this->setUp();
        Auth::logout();

        $res = $this->get('/dashboard');
        $this->assertEquals(302, $res->getStatusCode());
        $this->assertStringContains('/login', (string)$res->getHeader('Location'));
    }

    public function testMayorDashboardLoadsWithCommandCenter(): void
    {
        $this->setUp();
        $user = User::findByEmail('demo.mayor@demo.local');
        $this->assertNotNull($user, 'Demo Mayor user must exist');

        Auth::login($user);
        $res = $this->get('/dashboard');

        $this->assertEquals(200, $res->getStatusCode());
        $this->assertStringContains('MAYOR', $res->getContent());
        $this->assertStringContains('Executive KPIs', $res->getContent());
        $this->assertStringContains('Attention Required', $res->getContent());
    }

    public function testAdministratorDashboardLoads(): void
    {
        $this->setUp();
        $user = User::findByEmail('demo.administrator@demo.local');
        $this->assertNotNull($user, 'Demo Administrator user must exist');

        Auth::login($user);
        $res = $this->get('/dashboard');

        $this->assertEquals(200, $res->getStatusCode());
        $this->assertStringContains('ADMINISTRATOR', $res->getContent());
        $this->assertStringContains('Executive KPIs', $res->getContent());
    }

    public function testCeoDashboardLoads(): void
    {
        $this->setUp();
        $user = User::findByEmail('demo.ceo@demo.local');
        $this->assertNotNull($user, 'Demo CEO user must exist');

        Auth::login($user);
        $res = $this->get('/dashboard');

        $this->assertEquals(200, $res->getStatusCode());
        $this->assertStringContains('CEO', $res->getContent());
        $this->assertStringContains('Operational', $res->getContent());
    }

    public function testFieldWorkerDashboardLoadsWithTasks(): void
    {
        $this->setUp();
        $user = User::findByEmail('demo.field_worker@demo.local');
        $this->assertNotNull($user, 'Demo Field Worker user must exist');

        Auth::login($user);
        $res = $this->get('/dashboard');

        $this->assertEquals(200, $res->getStatusCode());
        $this->assertStringContains('FIELD WORKER', $res->getContent());
        $this->assertStringContains('আমার আজকের কাজ', $res->getContent());
    }

    public function testSupervisorDashboardLoadsWithVerificationQueue(): void
    {
        $this->setUp();
        $user = User::findByEmail('demo.supervisor@demo.local');
        $this->assertNotNull($user, 'Demo Supervisor user must exist');

        Auth::login($user);
        $res = $this->get('/dashboard');

        $this->assertEquals(200, $res->getStatusCode());
        $this->assertStringContains('SUPERVISOR', $res->getContent());
        $this->assertStringContains('সুপারভাইজার', $res->getContent());
    }

    public function testWardOfficerDashboardLoads(): void
    {
        $this->setUp();
        $user = User::findByEmail('demo.ward_officer@demo.local');
        $this->assertNotNull($user, 'Demo Ward Officer user must exist');

        Auth::login($user);
        $res = $this->get('/dashboard');

        $this->assertEquals(200, $res->getStatusCode());
        $this->assertStringContains('WARD OFFICER', $res->getContent());
    }

    public function testZoneOfficerDashboardLoadsWithApprovedWards(): void
    {
        $this->setUp();
        $user = User::findByEmail('demo.zone_officer@demo.local');
        $this->assertNotNull($user, 'Demo Zone Officer user must exist');

        Auth::login($user);
        $res = $this->get('/dashboard');

        $this->assertEquals(200, $res->getStatusCode());
        $this->assertStringContains('ZONE OFFICER', $res->getContent());
        $this->assertStringContains('১, ২, ৪, ৬, ১১, ১২, ২৭, ২৮, ২৯, ৩০', $res->getContent());
    }

    public function testPlatformSuperAdminDashboardLoadsNonTechnical(): void
    {
        $this->setUp();
        $user = User::findByEmail('demo.platform_super_admin@demo.local');
        $this->assertNotNull($user, 'Demo Platform Super Admin user must exist');

        Auth::login($user);
        $res = $this->get('/dashboard');

        $this->assertEquals(200, $res->getStatusCode());
        $this->assertStringContains('PLATFORM SUPER ADMIN', $res->getContent());
        $this->assertTrue(
            str_contains($res->getContent(), 'কর্মকর্তা ও জনবল') || str_contains($res->getContent(), 'People & Staff'),
            'Must contain People & Staff module'
        );
        $this->assertTrue(
            str_contains($res->getContent(), 'অঞ্চল ও ওয়ার্ড এলাকা') || str_contains($res->getContent(), 'Areas & Wards'),
            'Must contain Areas & Wards module'
        );
        $this->assertTrue(
            str_contains($res->getContent(), 'রাউটিং') || str_contains($res->getContent(), 'Routing'),
            'Must contain Routing module'
        );
    }

    public function testTechnicalSuperAdminDashboardLoadsTrafficLightHealth(): void
    {
        $this->setUp();
        $user = User::findByEmail('demo.technical_super_admin@demo.local');
        $this->assertNotNull($user, 'Demo Technical Super Admin user must exist');

        Auth::login($user);
        $res = $this->get('/dashboard');

        $this->assertEquals(200, $res->getStatusCode());
        $this->assertStringContains('TECHNICAL SUPER ADMIN', $res->getContent());
        $this->assertStringContains('HEALTHY', $res->getContent());
    }

    public function testRoleActionPermissionBoundaries(): void
    {
        $this->setUp();

        // 1. Citizen cannot start a field task
        $citizen = User::findByEmail('demo.citizen@demo.local');
        Auth::login($citizen);
        $res1 = $this->post('/dashboard/tasks/1/start', [
            '_csrf_token' => Security::generateCsrfToken(),
        ]);
        $this->assertEquals(403, $res1->getStatusCode());

        // 2. Field worker cannot verify a task
        $worker = User::findByEmail('demo.field_worker@demo.local');
        Auth::login($worker);
        $res2 = $this->post('/dashboard/tasks/1/verify', [
            '_csrf_token' => Security::generateCsrfToken(),
        ]);
        $this->assertEquals(403, $res2->getStatusCode());

        // 3. Councillor cannot issue executive directives
        $councillor = User::findByEmail('demo.general_councillor@demo.local');
        Auth::login($councillor);
        $res3 = $this->post('/dashboard/executive/directive', [
            '_csrf_token' => Security::generateCsrfToken(),
            'complaint_id' => 1,
            'instruction' => 'Directive test',
        ]);
        $this->assertEquals(403, $res3->getStatusCode());
    }
}
