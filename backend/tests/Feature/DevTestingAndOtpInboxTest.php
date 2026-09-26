<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Feature;

use AmarMayor\Auth\Otp\DevOtpInboxService;
use AmarMayor\Database\DatabaseManager;
use AmarMayor\Database\Seeders\DemoSeeder;
use AmarMayor\Support\Config;
use AmarMayor\Support\Security;
use AmarMayor\Tests\TestCase;
use PDO;
use RuntimeException;

class DevTestingAndOtpInboxTest extends TestCase
{
    public function testOtpDoesNotAppearInUrl(): void
    {
        $this->setUp();
        $phone = '01711' . random_int(100000, 999999);

        $res = $this->post('/auth/otp/request', [
            '_csrf_token' => Security::generateCsrfToken(),
            'phone' => $phone,
        ]);

        $this->assertEquals(302, $res->getStatusCode());
        $location = $res->getHeader('location') ?: $res->getHeader('Location');

        $this->assert(!empty($location), 'Location header must exist on redirect');
        $this->assert(!str_contains($location, 'mock_otp='), 'Redirect Location URL must NEVER contain mock_otp parameter');
        $this->assert(str_contains($location, '/login?step=verify'), 'Redirect Location should take user to clean verify step');
    }

    public function testDevOtpInboxWorksLocally(): void
    {
        $this->setUp();
        Config::set('app.env', 'local');
        $testPhone = '01711998822';

        // Trigger OTP request
        $this->post('/auth/otp/request', [
            '_csrf_token' => Security::generateCsrfToken(),
            'phone' => $testPhone,
        ]);

        // Load Developer OTP Inbox
        $inboxRes = $this->get('/dev/otp-inbox');
        $this->assertEquals(200, $inboxRes->getStatusCode());
        $this->assertStringContains('Mock OTP Verification Inbox', $inboxRes->getContent());
        $this->assertStringContains($testPhone, $inboxRes->getContent());
    }

    public function testDevRoutesAndSeederBlockedInProduction(): void
    {
        $this->setUp();
        Config::set('app.env', 'production');

        // 1. /dev/otp-inbox must return 404 in production
        $otpRes = $this->get('/dev/otp-inbox');
        $this->assertEquals(404, $otpRes->getStatusCode());

        // 2. /dev/testing-access must return 404 in production
        $accessRes = $this->get('/dev/testing-access');
        $this->assertEquals(404, $accessRes->getStatusCode());

        // 3. DemoSeeder must throw exception in production
        $threw = false;
        try {
            DemoSeeder::run();
        } catch (RuntimeException $e) {
            $threw = true;
        }
        $this->assert($threw, 'DemoSeeder must throw RuntimeException when app.env is production');

        // Restore config
        Config::set('app.env', 'local');
    }

    public function testTestingAccessPageLoadsLocally(): void
    {
        $this->setUp();
        Config::set('app.env', 'local');

        $res = $this->get('/dev/testing-access');
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertStringContains('Development Demo Accounts', $res->getContent());
        $this->assertStringContains('demo.mayor@demo.local', $res->getContent());
        $this->assertStringContains('demo.technical_super_admin@demo.local', $res->getContent());
    }

    public function testAllCanonicalDemoAccountsSeeded(): void
    {
        $this->setUp();
        $pdo = DatabaseManager::getConnection();

        $count = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE email LIKE '%@demo.local'")->fetchColumn();
        $this->assert($count >= 20, "At least 20 staff demo accounts must be seeded, got: {$count}");

        // Verify Platform Super Admin vs Technical Super Admin are distinct users
        $platId = (int)$pdo->query("SELECT id FROM users WHERE email = 'demo.platform_super_admin@demo.local'")->fetchColumn();
        $techId = (int)$pdo->query("SELECT id FROM users WHERE email = 'demo.technical_super_admin@demo.local'")->fetchColumn();

        $this->assert($platId > 0, 'Platform Super Admin demo account must exist');
        $this->assert($techId > 0, 'Technical Super Admin demo account must exist');
        $this->assert($platId !== $techId, 'Platform Super Admin and Technical Super Admin must be separate users');
    }

    public function testLoginPageRendersAll21CanonicalRolesAndTestingHubModal(): void
    {
        $this->setUp();
        Config::set('app.env', 'local');

        $res = $this->get('/login');
        $this->assertEquals(200, $res->getStatusCode());
        $content = $res->getContent();

        // 1. Verify Citizen Quick-fill Test accounts
        $this->assertStringContains('01711000001', $content);
        $this->assertStringContains('01711000002', $content);

        // 2. Verify Role & Ward Selectors
        $this->assertStringContains('id="roleSelector"', $content);
        $this->assertStringContains('id="wardSupervisorSelector"', $content);
        $this->assertStringContains('id="allRolesModal"', $content);

        // 3. Verify All 21 Canonical Roles present in login view
        $expectedRoles = [
            'demo.mayor@demo.local',
            'demo.administrator@demo.local',
            'demo.ceo@demo.local',
            'demo.platform_super_admin@demo.local',
            'demo.technical_super_admin@demo.local',
            'demo.general_councillor@demo.local',
            'demo.reserved_women_councillor@demo.local',
            'demo.responsible_officer@demo.local',
            'demo.ward_officer@demo.local',
            'demo.department_head@demo.local',
            'demo.department_officer@demo.local',
            'demo.zone_officer@demo.local',
            'demo.supervisor@demo.local',
            'demo.team_leader@demo.local',
            'demo.field_worker@demo.local',
            'demo.call_center_operator@demo.local',
            'demo.control_room_officer@demo.local',
            'demo.public_info_officer@demo.local',
            'demo.data_monitoring_officer@demo.local',
            'demo.auditor@demo.local',
            'demo.public_viewer@demo.local',
        ];

        foreach ($expectedRoles as $email) {
            $this->assertStringContains($email, $content, "Login page must include demo account: {$email}");
        }

        // 4. Test 1-click password authentication for a representative role (General Councillor)
        $loginRes = $this->post('/login/password', [
            'identifier' => 'demo.general_councillor@demo.local',
            'password' => 'Demo@12345',
            '_csrf_token' => \AmarMayor\Support\Security::generateCsrfToken(),
        ]);
        $this->assertEquals(302, $loginRes->getStatusCode());
        $this->assertEquals('/dashboard', (string)$loginRes->getHeader('Location'));
    }
}

