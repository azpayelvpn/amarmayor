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
        $phone = '01711998811';

        $res = $this->post('/auth/otp/request', [
            '_csrf_token' => Security::generateCsrfToken(),
            'phone' => $phone,
        ]);

        $this->assertEquals(302, $res->getStatusCode());
        $location = $res->getHeader('Location');

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
}
